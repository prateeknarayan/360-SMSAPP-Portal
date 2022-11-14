/**
 * DataTables Basic
 */

$(function () {
  'use strict';

  var dt_authtypes_table =  $('.datatables-authtype'),
    dt_channels_table = $('.datatables-channels'),
    dt_platforms_table = $('.datatables-platforms'),
    dt_records_table = $('.datatables-records'),
    dt_clients_table = $('.datatables-clients'),
    dt_numbers_table = $('.datatables-numbers'),
    dt_clientnumbers_table = $('.datatables-clientnumbers'),
    dt_permissions_table = $('.datatables-permissions'),
    assetPath = '../../../app-assets/';

  if ($('body').attr('data-framework') === 'laravel') {
    assetPath = $('body').attr('data-asset-path');
  }

  // DataTable for authtypes
  // --------------------------------------------------------------------

  if (dt_authtypes_table.length) {
    var dt_authtype= dt_authtypes_table.DataTable({
      ajax: 'http://local.laraveladmin/su/data/authentication-type-list',
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'id' }, // used for sorting so will hide this column
        { data: 'name' },
        { data: '' },
        { data: '' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          orderable: false,
          responsivePriority: 2,
          targets: 0
        },
        {
          // For Checkboxes
          targets: 1,
          orderable: false,
          responsivePriority: 3,
          render: function (data, type, full, meta) {
            return (
              '<div class="form-check"> <input class="form-check-input dt-checkboxes" type="checkbox" value="'+full['id']+'" id="checkbox' +
              data +'" name="deleteids[]"/><label class="form-check-label" for="checkbox' +
              data +
              '"></label></div>'
            );
          },
          checkboxes: {
            selectAllRender:
              '<div class="form-check"> <input class="form-check-input" type="checkbox" value="" id="checkboxSelectAll" /><label class="form-check-label" for="checkboxSelectAll"></label></div>'
          }
        },
        {
          targets: 2,
          visible: false
        },
        {
          // Label
          targets: -2,
          render: function (data, type, full, meta) {
            var $status_number = full['status'];
            var $status = {
              0: { title: 'Inactive', class: 'badge-light-danger' },
              1: { title: 'Active', class: ' badge-light-success' }
            };
            if (typeof $status[$status_number] === 'undefined') {
              return data;
            }
            return (
              '<span class="badge rounded-pill ' +
              $status[$status_number].class +
              '">' +
              $status[$status_number].title +
              '</span>'
            );
          }
        },
        {
          // Actions
          targets: -1,
          title: 'Actions',
          orderable: false,
          render: function (data, type, full, meta) {
            return (
              '<div class="d-inline-flex">' +
              '<a class="pe-1 dropdown-toggle hide-arrow text-primary" data-bs-toggle="dropdown">' +
              feather.icons['more-vertical'].toSvg({ class: 'font-small-4' }) +
              '</a>' +
              '<div class="dropdown-menu dropdown-menu-end">' +
              '<a href="javascript" class="dropdown-item delete-record">' +
              feather.icons['trash-2'].toSvg({ class: 'font-small-4 me-50' }) +
              'Delete</a>' +
              '</div>' +
              '</div>' +
              '<a href="http://local.laraveladmin/su/authentication-types/'+full['id']+'/edit" class="item-edit">' +
              feather.icons['edit'].toSvg({ class: 'font-small-4' }) +
              '</a>'
            );
          }
        }
      ],
      order: [[2, 'desc']],
      dom: '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-end"B>><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      displayLength: 7,
      lengthMenu: [7, 10, 25, 50, 75, 100],
      buttons: [
        {
          extend: 'collection',
          className: 'btn btn-outline-secondary dropdown-toggle me-2',
          text: feather.icons['share'].toSvg({ class: 'font-small-4 me-50' }) + 'Export',
          buttons: [
            {
              extend: 'print',
              text: feather.icons['printer'].toSvg({ class: 'font-small-4 me-50' }) + 'Print',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'csv',
              text: feather.icons['file-text'].toSvg({ class: 'font-small-4 me-50' }) + 'Csv',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'excel',
              text: feather.icons['file'].toSvg({ class: 'font-small-4 me-50' }) + 'Excel',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'pdf',
              text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 me-50' }) + 'Pdf',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'copy',
              text: feather.icons['copy'].toSvg({ class: 'font-small-4 me-50' }) + 'Copy',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            }
          ],
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
            $(node).parent().removeClass('btn-group');
            setTimeout(function () {
              $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
            }, 50);
          }
        },
        {
          text: feather.icons['plus'].toSvg({ class: 'me-50 font-small-4' }) + 'Add New',
          className: 'create-new btn btn-primary addAuthType me-2',
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
          }
        },
        {
          text: feather.icons['trash'].toSvg({ class: 'me-50 font-small-4' }) + 'Delete',
          className: 'deleteall btn btn-danger',
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
          }
        }
      ],
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['full_name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            var data = $.map(columns, function (col, i) {
              return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
                ? '<tr data-dt-row="' +
                    col.rowIdx +
                    '" data-dt-column="' +
                    col.columnIndex +
                    '">' +
                    '<td>' +
                    col.title +
                    ':' +
                    '</td> ' +
                    '<td>' +
                    col.data +
                    '</td>' +
                    '</tr>'
                : '';
            }).join('');

            return data ? $('<table class="table"/>').append('<tbody>' + data + '</tbody>') : false;
          }
        }
      },
      language: {
        paginate: {
          // remove previous & next text from pagination
          previous: '&nbsp;',
          next: '&nbsp;'
        }
      }
    });
    $( ".addAuthType" ).click(function() {
      window.location.replace("http://local.laraveladmin/su/authentication-types/create");
    });

    $( ".deleteall" ).click(function() {
      var token = $('[name="_token"]').val();
      var deleteids = [];
      $('[name="deleteids[]"]:checked').each(function () {
       var key = $(this).val();
       deleteids.push(key);
      });
      $.ajax({
          url: 'http://local.laraveladmin/su/authentication-types/delete/all',
          type: 'POST',
           data: {"numbers" : deleteids,'_token' : token},
          success: function(result) {
              location.reload();                                          
          }
      });
    });
  }

  // Datatables for Permissions
  if (dt_permissions_table.length) {
    var dt_authtype= dt_permissions_table.DataTable({
      ajax: 'http://local.laraveladmin/su/data/permissions-list',
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'id' }, // used for sorting so will hide this column
        { data: 'name' },
        { data: 'assigned_to' },
        { data: 'created_date' },
        { data: '' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          orderable: false,
          responsivePriority: 2,
          targets: 0
        },
        {
          // For Checkboxes
          targets: 1,
          orderable: false,
          responsivePriority: 3,
          render: function (data, type, full, meta) {
            return (
              '<div class="form-check"> <input class="form-check-input dt-checkboxes" type="checkbox" value="'+full['id']+'" id="checkbox' +
              data +'" name="deleteids[]"/><label class="form-check-label" for="checkbox' +
              data +
              '"></label></div>'
            );
          },
          checkboxes: {
            selectAllRender:
              '<div class="form-check"> <input class="form-check-input" type="checkbox" value="" id="checkboxSelectAll" /><label class="form-check-label" for="checkboxSelectAll"></label></div>'
          }
        },
        {
          targets: 2,
          visible: false
        },
        {
          // User Role
          targets: 4,
          orderable: false,
          render: function (data, type, full, meta) {
            var $assignedTo = full['assigned_to'],
              $output = '';
            var roleBadgeObj = {
              Admin:
                '<a href="' +
                userList +
                '" class="me-50"><span class="badge rounded-pill badge-light-primary">Administrator</span></a>',
              Manager:
                '<a href="' +
                userList +
                '" class="me-50"><span class="badge rounded-pill badge-light-warning">Manager</span></a>',
              Users:
                '<a href="' +
                userList +
                '" class="me-50"><span class="badge rounded-pill badge-light-success">Users</span></a>',
              Support:
                '<a href="' +
                userList +
                '" class="me-50"><span class="badge rounded-pill badge-light-info">Support</span></a>',
              Restricted:
                '<a href="' +
                userList +
                '" class="me-50"><span class="badge rounded-pill badge-light-danger">Restricted User</span></a>'
            };
            for (var i = 0; i < $assignedTo.length; i++) {
              var val = $assignedTo[i];
              $output += roleBadgeObj[val];
            }
            return $output;
          }
        },
        {
          // Actions
          targets: -1,
          title: 'Actions',
          orderable: false,
          render: function (data, type, full, meta) {
            return (
              '<div class="d-inline-flex">' +
              '<a class="pe-1 dropdown-toggle hide-arrow text-primary" data-bs-toggle="dropdown">' +
              feather.icons['more-vertical'].toSvg({ class: 'font-small-4' }) +
              '</a>' +
              '<div class="dropdown-menu dropdown-menu-end">' +
              '<a href="javascript" class="dropdown-item delete-record">' +
              feather.icons['trash-2'].toSvg({ class: 'font-small-4 me-50' }) +
              'Delete</a>' +
              '</div>' +
              '</div>' +
              '<a href="http://local.laraveladmin/su/authentication-types/'+full['id']+'/edit" class="item-edit">' +
              feather.icons['edit'].toSvg({ class: 'font-small-4' }) +
              '</a>'
            );
          }
        }
      ],
      order: [[2, 'desc']],
      dom: '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-end"B>><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      displayLength: 7,
      lengthMenu: [7, 10, 25, 50, 75, 100],
      buttons: [
        {
          extend: 'collection',
          className: 'btn btn-outline-secondary dropdown-toggle me-2',
          text: feather.icons['share'].toSvg({ class: 'font-small-4 me-50' }) + 'Export',
          buttons: [
            {
              extend: 'print',
              text: feather.icons['printer'].toSvg({ class: 'font-small-4 me-50' }) + 'Print',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'csv',
              text: feather.icons['file-text'].toSvg({ class: 'font-small-4 me-50' }) + 'Csv',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'excel',
              text: feather.icons['file'].toSvg({ class: 'font-small-4 me-50' }) + 'Excel',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'pdf',
              text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 me-50' }) + 'Pdf',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'copy',
              text: feather.icons['copy'].toSvg({ class: 'font-small-4 me-50' }) + 'Copy',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            }
          ],
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
            $(node).parent().removeClass('btn-group');
            setTimeout(function () {
              $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
            }, 50);
          }
        },
        {
          text: feather.icons['plus'].toSvg({ class: 'me-50 font-small-4' }) + 'Add New',
          className: 'create-new btn btn-primary addAuthType me-2',
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
          }
        },
        {
          text: feather.icons['trash'].toSvg({ class: 'me-50 font-small-4' }) + 'Delete',
          className: 'deleteall btn btn-danger',
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
          }
        }
      ],
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['full_name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            var data = $.map(columns, function (col, i) {
              return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
                ? '<tr data-dt-row="' +
                    col.rowIdx +
                    '" data-dt-column="' +
                    col.columnIndex +
                    '">' +
                    '<td>' +
                    col.title +
                    ':' +
                    '</td> ' +
                    '<td>' +
                    col.data +
                    '</td>' +
                    '</tr>'
                : '';
            }).join('');

            return data ? $('<table class="table"/>').append('<tbody>' + data + '</tbody>') : false;
          }
        }
      },
      language: {
        paginate: {
          // remove previous & next text from pagination
          previous: '&nbsp;',
          next: '&nbsp;'
        }
      }
    });
    $( ".addAuthType" ).click(function() {
      window.location.replace("http://local.laraveladmin/su/authentication-types/create");
    });

    $( ".deleteall" ).click(function() {
      var token = $('[name="_token"]').val();
      var deleteids = [];
      $('[name="deleteids[]"]:checked').each(function () {
       var key = $(this).val();
       deleteids.push(key);
      });
      $.ajax({
          url: 'http://local.laraveladmin/su/authentication-types/delete/all',
          type: 'POST',
           data: {"numbers" : deleteids,'_token' : token},
          success: function(result) {
              location.reload();                                          
          }
      });
    });
  }

  // DataTable for channels
  // --------------------------------------------------------------------

  if (dt_channels_table.length) {
    var dt_channels = dt_channels_table.DataTable({
      ajax: 'http://local.laraveladmin/su/data/channels-list',
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'id' }, // used for sorting so will hide this column
        { data: 'name' },
        { data: '' },
        { data: '' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          orderable: false,
          responsivePriority: 2,
          targets: 0
        },
        {
          // For Checkboxes
          targets: 1,
          orderable: false,
          responsivePriority: 3,
          render: function (data, type, full, meta) {
            return (
              '<div class="form-check"> <input class="form-check-input dt-checkboxes" type="checkbox" value="'+full['id']+'" id="checkbox' +
              data +'" name="deleteids[]"/><label class="form-check-label" for="checkbox' +
              data +
              '"></label></div>'
            );
          },
          checkboxes: {
            selectAllRender:
              '<div class="form-check"> <input class="form-check-input" type="checkbox" value="" id="checkboxSelectAll" /><label class="form-check-label" for="checkboxSelectAll"></label></div>'
          }
        },
        {
          targets: 2,
          visible: false
        },
        {
          // Label
          targets: -2,
          render: function (data, type, full, meta) {
            var $status_number = full['status'];
            var $status = {
              0: { title: 'Inactive', class: 'badge-light-danger' },
              1: { title: 'Active', class: ' badge-light-success' }
            };
            if (typeof $status[$status_number] === 'undefined') {
              return data;
            }
            return (
              '<span class="badge rounded-pill ' +
              $status[$status_number].class +
              '">' +
              $status[$status_number].title +
              '</span>'
            );
          }
        },
        {
          // Actions
          targets: -1,
          title: 'Actions',
          orderable: false,
          render: function (data, type, full, meta) {
            return (
              '<div class="d-inline-flex">' +
              '<a class="pe-1 dropdown-toggle hide-arrow text-primary" data-bs-toggle="dropdown">' +
              feather.icons['more-vertical'].toSvg({ class: 'font-small-4' }) +
              '</a>' +
              '<div class="dropdown-menu dropdown-menu-end">' +
              '<a href="javascript:;" class="dropdown-item delete-record">' +
              feather.icons['trash-2'].toSvg({ class: 'font-small-4 me-50' }) +
              'Delete</a>' +
              '</div>' +
              '</div>' +
              '<a href="javascript:;" class="item-edit viewdetails" data-id="'+full['id']+'" data-name="'+full['name']+'" data-status="'+full['status']+'">' +
              feather.icons['edit'].toSvg({ class: 'font-small-4' }) +
              '</a>'
            );
          }
        }
      ],
      order: [[2, 'desc']],
      dom: '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-end"B>><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      displayLength: 7,
      lengthMenu: [7, 10, 25, 50, 75, 100],
      buttons: [
        {
          extend: 'collection',
          className: 'btn btn-outline-secondary dropdown-toggle me-2',
          text: feather.icons['share'].toSvg({ class: 'font-small-4 me-50' }) + 'Export',
          buttons: [
            {
              extend: 'print',
              text: feather.icons['printer'].toSvg({ class: 'font-small-4 me-50' }) + 'Print',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'csv',
              text: feather.icons['file-text'].toSvg({ class: 'font-small-4 me-50' }) + 'Csv',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'excel',
              text: feather.icons['file'].toSvg({ class: 'font-small-4 me-50' }) + 'Excel',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'pdf',
              text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 me-50' }) + 'Pdf',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'copy',
              text: feather.icons['copy'].toSvg({ class: 'font-small-4 me-50' }) + 'Copy',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            }
          ],
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
            $(node).parent().removeClass('btn-group');
            setTimeout(function () {
              $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
            }, 50);
          }
        },
        {
          text: feather.icons['plus'].toSvg({ class: 'me-50 font-small-4' }) + 'Add New',
          className: 'create-new btn btn-primary me-2',
          attr: {
            'data-bs-toggle': 'modal',
            'data-bs-target': '#modals-slide-in'
          },
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
          }
        },
        {
          text: feather.icons['trash'].toSvg({ class: 'me-50 font-small-4' }) + 'Delete',
          className: 'deleteall btn btn-danger',
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
          }
        }
      ],
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['full_name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            var data = $.map(columns, function (col, i) {
              return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
                ? '<tr data-dt-row="' +
                    col.rowIdx +
                    '" data-dt-column="' +
                    col.columnIndex +
                    '">' +
                    '<td>' +
                    col.title +
                    ':' +
                    '</td> ' +
                    '<td>' +
                    col.data +
                    '</td>' +
                    '</tr>'
                : '';
            }).join('');

            return data ? $('<table class="table"/>').append('<tbody>' + data + '</tbody>') : false;
          }
        }
      },
      language: {
        paginate: {
          // remove previous & next text from pagination
          previous: '&nbsp;',
          next: '&nbsp;'
        }
      }
    });

    $( ".deleteall" ).click(function() {
      var token = $('[name="_token"]').val();
      var deleteids = [];
      $('[name="deleteids[]"]:checked').each(function () {
       var key = $(this).val();
       deleteids.push(key);
      });
      $.ajax({
          url: 'http://local.laraveladmin/su/channels/delete/all',
          type: 'POST',
           data: {"numbers" : deleteids,'_token' : token},
          success: function(result) {
              location.reload();                                          
          }
      });
    });
  }

   // DataTable for Supported Platforms
  // --------------------------------------------------------------------

  if (dt_platforms_table.length) {
    var dt_platforms = dt_platforms_table.DataTable({
      ajax: 'http://local.laraveladmin/su/data/platforms-list',
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'id' }, // used for sorting so will hide this column
        { data: 'name' },
        { data: 'code' },
        { data: '' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          orderable: false,
          responsivePriority: 2,
          targets: 0
        },
        {
          // For Checkboxes
          targets: 1,
          orderable: false,
          responsivePriority: 3,
          render: function (data, type, full, meta) {
            return (
              '<div class="form-check"> <input class="form-check-input dt-checkboxes" type="checkbox" value="'+full['id']+'" id="checkbox' +
              data +'" name="deleteids[]"/><label class="form-check-label" for="checkbox' +
              data +
              '"></label></div>'
            );
          },
          checkboxes: {
            selectAllRender:
              '<div class="form-check"> <input class="form-check-input" type="checkbox" value="" id="checkboxSelectAll" /><label class="form-check-label" for="checkboxSelectAll"></label></div>'
          }
        },
        {
          targets: 2,
          visible: false
        },
        {
          // Actions
          targets: -1,
          title: 'Actions',
          orderable: false,
          render: function (data, type, full, meta) {
            return (
              '<div class="d-inline-flex">' +
              '<a class="pe-1 dropdown-toggle hide-arrow text-primary" data-bs-toggle="dropdown">' +
              feather.icons['more-vertical'].toSvg({ class: 'font-small-4' }) +
              '</a>' +
              '<div class="dropdown-menu dropdown-menu-end">' +
              '<a href="javascript:;" class="dropdown-item delete-record">' +
              feather.icons['trash-2'].toSvg({ class: 'font-small-4 me-50' }) +
              'Delete</a>' +
              '</div>' +
              '</div>' +
              '<a href="javascript:;" class="item-edit">' +
              feather.icons['edit'].toSvg({ class: 'font-small-4' }) +
              '</a>'
            );
          }
        }
      ],
      order: [[2, 'desc']],
      dom: '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-end"B>><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      displayLength: 7,
      lengthMenu: [7, 10, 25, 50, 75, 100],
      buttons: [
        {
          extend: 'collection',
          className: 'btn btn-outline-secondary dropdown-toggle me-2',
          text: feather.icons['share'].toSvg({ class: 'font-small-4 me-50' }) + 'Export',
          buttons: [
            {
              extend: 'print',
              text: feather.icons['printer'].toSvg({ class: 'font-small-4 me-50' }) + 'Print',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'csv',
              text: feather.icons['file-text'].toSvg({ class: 'font-small-4 me-50' }) + 'Csv',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'excel',
              text: feather.icons['file'].toSvg({ class: 'font-small-4 me-50' }) + 'Excel',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'pdf',
              text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 me-50' }) + 'Pdf',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'copy',
              text: feather.icons['copy'].toSvg({ class: 'font-small-4 me-50' }) + 'Copy',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            }
          ],
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
            $(node).parent().removeClass('btn-group');
            setTimeout(function () {
              $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
            }, 50);
          }
        },
        {
          text: feather.icons['plus'].toSvg({ class: 'me-50 font-small-4' }) + 'Add New',
          className: 'create-new btn btn-primary addPlatform me-2',

          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
          }
        },
        {
          text: feather.icons['trash'].toSvg({ class: 'me-50 font-small-4' }) + 'Delete',
          className: 'deleteall btn btn-danger',
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
          }
        }
      ],
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['full_name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            var data = $.map(columns, function (col, i) {
              return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
                ? '<tr data-dt-row="' +
                    col.rowIdx +
                    '" data-dt-column="' +
                    col.columnIndex +
                    '">' +
                    '<td>' +
                    col.title +
                    ':' +
                    '</td> ' +
                    '<td>' +
                    col.data +
                    '</td>' +
                    '</tr>'
                : '';
            }).join('');

            return data ? $('<table class="table"/>').append('<tbody>' + data + '</tbody>') : false;
          }
        }
      },
      language: {
        paginate: {
          // remove previous & next text from pagination
          previous: '&nbsp;',
          next: '&nbsp;'
        }
      }
    });
  //add platform
  $( ".addPlatform" ).click(function() {
      window.location.replace("http://local.laraveladmin/su/supported-platforms/create");
  });

  $( ".deleteall" ).click(function() {
      var token = $('[name="_token"]').val();
      var deleteids = [];
      $('[name="deleteids[]"]:checked').each(function () {
       var key = $(this).val();
       deleteids.push(key);
      });
      $.ajax({
          url: 'http://local.laraveladmin/su/supported-platforms/delete/all',
          type: 'POST',
           data: {"numbers" : deleteids,'_token' : token},
          success: function(result) {
              location.reload();                                          
          }
      });
    });
  }

  // DataTable for Process Records
  // --------------------------------------------------------------------

  if (dt_records_table.length) {
    var dt_records = dt_records_table.DataTable({
      ajax: 'http://local.laraveladmin/data/records-list',
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'id' }, // used for sorting so will hide this column
        { data: 'platform' },
        { data: 'destination_result' },
        { data: 'created_at' },
        { data: 'status' },
        { data: '' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          orderable: false,
          responsivePriority: 2,
          targets: 0
        },
        {
          // For Checkboxes
          targets: 1,
          orderable: false,
          responsivePriority: 3,
          render: function (data, type, full, meta) {
             return (
              '<div class="form-check"> <input class="form-check-input dt-checkboxes" type="checkbox" value="'+full['id']+'" id="checkbox' +
              data +'" name="deleteids[]"/><label class="form-check-label" for="checkbox' +
              data +
              '"></label></div>'
            );
          },
          checkboxes: {
            selectAllRender:
              '<div class="form-check"> <input class="form-check-input" type="checkbox" value="" id="checkboxSelectAll" /><label class="form-check-label" for="checkboxSelectAll"></label></div>'
          }
        },
        {
         // Label
          targets: -2,
          render: function (data, type, full, meta) {
            var $status_number = full['status'];
            var $status = {
              'Pending': { title: 'Pending', class: 'badge-light-warning' },
              'Error': { title: 'Error', class: 'badge-light-danger' },
              'Success': { title: 'Success', class: ' badge-light-success' }
            };
            if (typeof $status[$status_number] === 'undefined') {
              return data;
            }
            return (
              '<span class="badge rounded-pill ' +
              $status[$status_number].class +
              '">' +
              $status[$status_number].title +
              '</span>'
            );
          }
        },
        {
          // Actions
          targets: -1,
          title: 'Actions',
          orderable: false,
          render: function (data, type, full, meta) {
            return (
              '<div class="d-inline-flex">' +
              '<a class="pe-1 dropdown-toggle hide-arrow text-primary" data-bs-toggle="dropdown">' +
              feather.icons['more-vertical'].toSvg({ class: 'font-small-4' }) +
              '</a>' +
              '<div class="dropdown-menu dropdown-menu-end">' +
              '<a href="http://local.laraveladmin/process-records/'+full['id']+'" class="dropdown-item">' +
              feather.icons['file-text'].toSvg({ class: 'font-small-4 me-50' }) +
              'Details</a>' +
 
              '<a href="javascript:;" class="dropdown-item delete-record">' +
              feather.icons['trash-2'].toSvg({ class: 'font-small-4 me-50' }) +
              'Delete</a>' +
              '</div>' +
              '</div>' +
              '<a href="javascript:;" class="item-edit">' +
              feather.icons['edit'].toSvg({ class: 'font-small-4' }) +
              '</a>'
            );
          }
        }

      ],
      order: [[2, 'desc']],
      dom: '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-end"B>><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      displayLength: 7,
      lengthMenu: [7, 10, 25, 50, 75, 100],
      buttons: [
        {
          extend: 'collection',
          className: 'btn btn-outline-secondary dropdown-toggle me-2',
          text: feather.icons['share'].toSvg({ class: 'font-small-4 me-50' }) + 'Export',
          buttons: [
            {
              extend: 'print',
              text: feather.icons['printer'].toSvg({ class: 'font-small-4 me-50' }) + 'Print',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'csv',
              text: feather.icons['file-text'].toSvg({ class: 'font-small-4 me-50' }) + 'Csv',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'excel',
              text: feather.icons['file'].toSvg({ class: 'font-small-4 me-50' }) + 'Excel',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'pdf',
              text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 me-50' }) + 'Pdf',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'copy',
              text: feather.icons['copy'].toSvg({ class: 'font-small-4 me-50' }) + 'Copy',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            }
          ],
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
            $(node).parent().removeClass('btn-group');
            setTimeout(function () {
              $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
            }, 50);
          }
        }
      ],
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['full_name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            var data = $.map(columns, function (col, i) {
              return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
                ? '<tr data-dt-row="' +
                    col.rowIdx +
                    '" data-dt-column="' +
                    col.columnIndex +
                    '">' +
                    '<td>' +
                    col.title +
                    ':' +
                    '</td> ' +
                    '<td>' +
                    col.data +
                    '</td>' +
                    '</tr>'
                : '';
            }).join('');

            return data ? $('<table class="table"/>').append('<tbody>' + data + '</tbody>') : false;
          }
        }
      },
      language: {
        paginate: {
          // remove previous & next text from pagination
          previous: '&nbsp;',
          next: '&nbsp;'
        }
      }
    });

  }

  // DataTable for Clients 
  // --------------------------------------------------------------------

  if (dt_clients_table.length) {
    var dt_records = dt_clients_table.DataTable({
      ajax: 'http://local.laraveladmin/data/clients-list',
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'id' }, // used for sorting so will hide this column
        { data: 'client_name' },
        { data: 'org_id' },
        { data: 'org_type' },
        { data: 'allow_security_flag' },
        { data: '' },
        { data: '' },
        { data: '' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          orderable: false,
          responsivePriority: 2,
          targets: 0,
          visible: false
        },
        {
          // For Checkboxes
          targets: 1,
          orderable: false,
          responsivePriority: 3,
          render: function (data, type, full, meta) {
            return (
              '<div class="form-check"> <input class="form-check-input dt-checkboxes" type="checkbox" value="' +
              data +'" id="checkbox' +
              data +
              '" /><label class="form-check-label" for="checkbox' +
              data +
              '"></label></div>'
            );
          },
          checkboxes: {
            selectAllRender:
              '<div class="form-check"> <input class="form-check-input" type="checkbox" value="" id="checkboxSelectAll" /><label class="form-check-label" for="checkboxSelectAll"></label></div>'
          }
        },
        {
          targets: 2,
          visible: false
        },
        {
         // Label
          targets: -3,
          render: function (data, type, full, meta) {
            return (
              '<a href="http://local.laraveladmin/clients/'+full['id']+'/numbers"><span class="badge bg-primary rounded-pill">Numbers ('+full['numbers']+')</span></a>'
            );
          }
        },
        {
         // Label
          targets: -2,
          render: function (data, type, full, meta) {
            var $status_number = full['status'];
            var $status = {
              '0': { title: 'Inactive', class: 'badge-light-warning' },
              '1': { title: 'Active', class: ' badge-light-success' }
            };
            if (typeof $status[$status_number] === 'undefined') {
              return data;
            }
            return (
              '<span class="badge rounded-pill ' +
              $status[$status_number].class +
              '">' +
              $status[$status_number].title +
              '</span>'
            );
          }
        },
        {
          // Actions
          targets: -1,
          title: 'Actions',
          orderable: false,
          render: function (data, type, full, meta) {
            return (
              '<div class="d-inline-flex">' +
              '<a class="pe-1 dropdown-toggle hide-arrow text-primary" data-bs-toggle="dropdown">' +
              feather.icons['more-vertical'].toSvg({ class: 'font-small-4' }) +
              '</a>' +
              '<div class="dropdown-menu dropdown-menu-end">' +
              '<a href="javascript:;" class="dropdown-item">' +
              feather.icons['file-text'].toSvg({ class: 'font-small-4 me-50' }) +
              'Details</a>' +
              '<a href="javascript:;" class="dropdown-item delete-record">' +
              feather.icons['trash-2'].toSvg({ class: 'font-small-4 me-50' }) +
              'Delete</a>' +
              '</div>' +
              '</div>' +
              '<a href="http://local.laraveladmin/clients/'+full['id']+'/edit" class="item-edit">' +
              feather.icons['edit'].toSvg({ class: 'font-small-4' }) +
              '</a>'
            );
          }
        }

      ],
      order: [[2, 'desc']],
      dom: '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-end"B>><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      displayLength: 7,
      lengthMenu: [7, 10, 25, 50, 75, 100],
      buttons: [
        {
          extend: 'collection',
          className: 'btn btn-outline-secondary dropdown-toggle me-2',
          text: feather.icons['share'].toSvg({ class: 'font-small-4 me-50' }) + 'Export',
          buttons: [
            {
              extend: 'print',
              text: feather.icons['printer'].toSvg({ class: 'font-small-4 me-50' }) + 'Print',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'csv',
              text: feather.icons['file-text'].toSvg({ class: 'font-small-4 me-50' }) + 'Csv',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'excel',
              text: feather.icons['file'].toSvg({ class: 'font-small-4 me-50' }) + 'Excel',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'pdf',
              text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 me-50' }) + 'Pdf',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'copy',
              text: feather.icons['copy'].toSvg({ class: 'font-small-4 me-50' }) + 'Copy',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            }
          ],
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
            $(node).parent().removeClass('btn-group');
            setTimeout(function () {
              $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
            }, 50);
          }
        },
        {
          text: feather.icons['plus'].toSvg({ class: 'me-50 font-small-4' }) + 'Add New',
          className: 'create-new btn btn-primary newClientadd me-2',
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
          }
        },
        {
          text: feather.icons['trash'].toSvg({ class: 'me-50 font-small-4' }) + 'Delete',
          className: 'deleteall btn btn-danger me-2',
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
          }
        },
        {
          text: feather.icons['download'].toSvg({ class: 'me-50 font-small-4' }) + 'Import',
          className: 'deleteall btn btn-warning',
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
          }
        }
      ],
      language: {
        paginate: {
          // remove previous & next text from pagination
          previous: '&nbsp;',
          next: '&nbsp;'
        }
      }
    });
    $( ".newClientadd" ).click(function() {
      window.location.replace("http://local.laraveladmin/clients/create");
    });
    $( ".deleteall" ).click(function() {
      var token = $('[name="_token"]').val();

      var deleteids = [];
      $('[name="deleteids[]"]:checked').each(function () {
       var key = $(this).val();
       deleteids.push(key);
      });
      $.ajax({
          url: 'http://local.laraveladmin/clients/delete/all',
          type: 'POST',
           data: {"numbers" : deleteids,'_token' : token},
          success: function(result) {
              location.reload();                                          
          }
      });
    });
  }

  // DataTable for Numbers 
  // --------------------------------------------------------------------

  if (dt_numbers_table.length) {
    var dt_numbers = dt_numbers_table.DataTable({
      ajax: 'http://local.laraveladmin/data/numbers-list',
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'id' }, // used for sorting so will hide this column
        { data: '' },
        { data: '' },
        { data: '' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          orderable: false,
          responsivePriority: 2,
          targets: 0
        },
        {
          targets: 2,
          visible: false
        },
        {
          // For Checkboxes
          targets: 1,
          orderable: false,
          responsivePriority: 3,
          render: function (data, type, full, meta) {
            return (
              '<div class="form-check"> <input class="form-check-input dt-checkboxes" type="checkbox" value="'+full['id']+'" id="checkbox' +
              data +'" name="deleteids[]"/><label class="form-check-label" for="checkbox' +
              data +
              '"></label></div>'
            );
          },
          checkboxes: {
            selectAllRender:
              '<div class="form-check"> <input class="form-check-input" type="checkbox" value="" id="checkboxSelectAll" /><label class="form-check-label" for="checkboxSelectAll"></label></div>'
          }
        },
        {
         // Label
          targets: 3,
          render: function (data, type, full, meta) {
            return (
              ''+full['number']+'<br/>\
              <p><strong>SId : </strong>'+full['number_sid']+'<br/><strong>Token : </strong>'+full['token']+'</p>'
            );
          }
        },
        {
         // Label
          targets: -2,
          render: function (data, type, full, meta) {
            return (
              '<a href=""><span class="badge bg-primary rounded-pill">'+full['clientname']+'</span></a>'
            );
          }
        },
        {
          // Actions
          targets: -1,
          title: 'Actions',
          orderable: false,
          render: function (data, type, full, meta) {
            return (
              '<div class="d-inline-flex">' +
              '<a class="pe-1 dropdown-toggle hide-arrow text-primary" data-bs-toggle="dropdown">' +
              feather.icons['more-vertical'].toSvg({ class: 'font-small-4' }) +
              '</a>' +
              '<div class="dropdown-menu dropdown-menu-end">' +
              '<a href="javascript:;" class="dropdown-item">' +
              feather.icons['file-text'].toSvg({ class: 'font-small-4 me-50' }) +
              'Details</a>' +
              '<a href="javascript:;" class="dropdown-item delete-record">' +
              feather.icons['trash-2'].toSvg({ class: 'font-small-4 me-50' }) +
              'Delete</a>' +
              '</div>' +
              '</div>' +
              '<a href="javascript:;" class="item-edit">' +
              feather.icons['edit'].toSvg({ class: 'font-small-4' }) +
              '</a>'
            );
          }
        }

      ],
      order: [[2, 'desc']],
      dom: '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-end"B>><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      displayLength: 7,
      lengthMenu: [7, 10, 25, 50, 75, 100],
      buttons: [
        {
          extend: 'collection',
          className: 'btn btn-outline-secondary dropdown-toggle me-2',
          text: feather.icons['share'].toSvg({ class: 'font-small-4 me-50' }) + 'Export',
          buttons: [
            {
              extend: 'print',
              text: feather.icons['printer'].toSvg({ class: 'font-small-4 me-50' }) + 'Print',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'csv',
              text: feather.icons['file-text'].toSvg({ class: 'font-small-4 me-50' }) + 'Csv',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'excel',
              text: feather.icons['file'].toSvg({ class: 'font-small-4 me-50' }) + 'Excel',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'pdf',
              text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 me-50' }) + 'Pdf',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'copy',
              text: feather.icons['copy'].toSvg({ class: 'font-small-4 me-50' }) + 'Copy',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            }
          ],
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
            $(node).parent().removeClass('btn-group');
            setTimeout(function () {
              $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
            }, 50);
          }
        },
        {
          text: feather.icons['plus'].toSvg({ class: 'me-50 font-small-4' }) + 'Add New',
          className: 'create-new btn btn-primary me-2',
          attr: {
            'data-bs-toggle': 'modal',
            'data-bs-target': '#modals-slide-in'
          },
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
          }
        },
        {
          text: feather.icons['trash'].toSvg({ class: 'me-50 font-small-4' }) + 'Delete',
          className: 'deleteall btn btn-danger',
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
          }
        }
      ],
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['full_name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            var data = $.map(columns, function (col, i) {
              return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
                ? '<tr data-dt-row="' +
                    col.rowIdx +
                    '" data-dt-column="' +
                    col.columnIndex +
                    '">' +
                    '<td>' +
                    col.title +
                    ':' +
                    '</td> ' +
                    '<td>' +
                    col.data +
                    '</td>' +
                    '</tr>'
                : '';
            }).join('');

            return data ? $('<table class="table"/>').append('<tbody>' + data + '</tbody>') : false;
          }
        }
      },
      language: {
        paginate: {
          // remove previous & next text from pagination
          previous: '&nbsp;',
          next: '&nbsp;'
        }
      }
    });
    
    //delete numbers in bulk
    $( ".deleteall" ).click(function() {
      var token = $('[name="_token"]').val();
      var deleteids = [];
      $('[name="deleteids[]"]:checked').each(function () {
       var key = $(this).val();
       deleteids.push(key);
      });
      $.ajax({
          url: 'http://local.laraveladmin/numbers/delete/all',
          type: 'POST',
           data: {"numbers" : deleteids,'_token' : token},
          success: function(result) {
              location.reload();                                          
          }
      });
    });
  }

  // DataTable for ClientNumbers 
  // --------------------------------------------------------------------

  if (dt_clientnumbers_table.length) {
    var clientid = $('#clientid').val();
    var dt_cnumbers = dt_clientnumbers_table.DataTable({
      ajax: 'http://local.laraveladmin/clients/'+clientid+'/numbers-list',
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'id' }, // used for sorting so will hide this column
        { data: '' },
        { data: '' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          orderable: false,
          responsivePriority: 2,
          targets: 0
        },
        {
          targets: 2,
          visible: false
        },
        {
         // Label
          targets: 3,
          render: function (data, type, full, meta) {
            return (
              ''+full['number']+'<br/>\
              <p><strong>SId : </strong>'+full['number_sid']+'<br/><strong>Token : </strong>'+full['token']+'</p>'
            );
          }
        },
        {
          // For Checkboxes
          targets: 1,
          orderable: false,
          responsivePriority: 3,
          render: function (data, type, full, meta) {
            return (
              '<div class="form-check"> <input class="form-check-input dt-checkboxes" type="checkbox" value="'+full['id']+'" id="checkbox' +
              data +'" name="deleteids[]"/><label class="form-check-label" for="checkbox' +
              data +
              '"></label></div>'
            );
          },
          checkboxes: {
            selectAllRender:
              '<div class="form-check"> <input class="form-check-input" type="checkbox" value="" id="checkboxSelectAll" /><label class="form-check-label" for="checkboxSelectAll"></label></div>'
          }
        },
        {
          // Actions
          targets: -1,
          title: 'Actions',
          orderable: false,
          render: function (data, type, full, meta) {
            return (
              '<div class="d-inline-flex">' +
              '<a class="pe-1 dropdown-toggle hide-arrow text-primary" data-bs-toggle="dropdown">' +
              feather.icons['more-vertical'].toSvg({ class: 'font-small-4' }) +
              '</a>' +
              '<div class="dropdown-menu dropdown-menu-end">' +
              '<a href="javascript:;" class="dropdown-item delete-record">' +
              feather.icons['trash-2'].toSvg({ class: 'font-small-4 me-50' }) +
              'Delete</a>' +
              '</div>' +
              '</div>' +
              '<a href="javascript:;" class="item-edit">' +
              feather.icons['edit'].toSvg({ class: 'font-small-4' }) +
              '</a>'
            );
          }
        }

      ],
      order: [[2, 'desc']],
      dom: '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-end"B>><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      displayLength: 7,
      lengthMenu: [7, 10, 25, 50, 75, 100],
      buttons: [
        {
          extend: 'collection',
          className: 'btn btn-outline-secondary dropdown-toggle me-2',
          text: feather.icons['share'].toSvg({ class: 'font-small-4 me-50' }) + 'Export',
          buttons: [
            {
              extend: 'print',
              text: feather.icons['printer'].toSvg({ class: 'font-small-4 me-50' }) + 'Print',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'csv',
              text: feather.icons['file-text'].toSvg({ class: 'font-small-4 me-50' }) + 'Csv',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'excel',
              text: feather.icons['file'].toSvg({ class: 'font-small-4 me-50' }) + 'Excel',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'pdf',
              text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 me-50' }) + 'Pdf',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'copy',
              text: feather.icons['copy'].toSvg({ class: 'font-small-4 me-50' }) + 'Copy',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            }
          ],
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
            $(node).parent().removeClass('btn-group');
            setTimeout(function () {
              $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
            }, 50);
          }
        },
        {
          text: feather.icons['plus'].toSvg({ class: 'me-50 font-small-4' }) + 'Add New',
          className: 'create-new btn btn-primary newcnumber me-2',
          attr: {
            'data-bs-toggle': 'modal',
            'data-bs-target': '#modals-slide-in'
          },
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
          }
        },
        {
          text: feather.icons['trash'].toSvg({ class: 'me-50 font-small-4' }) + 'Delete',
          className: 'deleteall btn btn-danger',
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
          }
        }
      ],
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['full_name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            var data = $.map(columns, function (col, i) {
              return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
                ? '<tr data-dt-row="' +
                    col.rowIdx +
                    '" data-dt-column="' +
                    col.columnIndex +
                    '">' +
                    '<td>' +
                    col.title +
                    ':' +
                    '</td> ' +
                    '<td>' +
                    col.data +
                    '</td>' +
                    '</tr>'
                : '';
            }).join('');

            return data ? $('<table class="table"/>').append('<tbody>' + data + '</tbody>') : false;
          }
        }
      },
      language: {
        paginate: {
          // remove previous & next text from pagination
          previous: '&nbsp;',
          next: '&nbsp;'
        }
      }
    });

    $( ".newcnumber" ).click(function() {
      window.location.replace("http://local.laraveladmin/clients/"+clientid+"/numbers/create");
    });

    $( ".deleteall" ).click(function() {
      var token = $('[name="_token"]').val();
      var deleteids = [];
      $('[name="deleteids[]"]:checked').each(function () {
       var key = $(this).val();
       deleteids.push(key);
      });
      $.ajax({
          url: 'http://local.laraveladmin/numbers/delete/all',
          type: 'POST',
           data: {"numbers" : deleteids,'_token' : token},
          success: function(result) {
              location.reload();                                          
          }
      });
    });
  }
});
