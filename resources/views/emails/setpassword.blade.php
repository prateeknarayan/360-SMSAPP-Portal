<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>Welcome</title>
      <!-- Fonts -->
      <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
      <!-- Styles -->
      <style>
         html, body {
         background-color: #fff;
         color: #636b6f;
         font-family: 'Raleway';
         font-weight: 100;
         height: 100vh;
         margin: 0;
         }
         .full-height {
         height: 100vh;
         }
         .flex-center {
         align-items: center;
         display: flex;
         justify-content: center;
         }
         .position-ref {
         position: relative;
         }
         .top-right {
         position: absolute;
         right: 10px;
         top: 18px;
         }
         .content {
         text-align: center;
         }
         .links > a {
         color: #636b6f;
         padding: 0 25px;
         font-size: 12px;
         font-weight: 600;
         letter-spacing: .1rem;
         text-decoration: none;
         text-transform: uppercase;
         }
         .m-b-md {
         margin-bottom: 30px;
         }
      </style>
   </head>
   <body>
      <div class="flex-center position-ref full-height">
         <div class="content container">
            <div class="text-center">
               XYZ Admin Panel
            </div>
            <div class="title m-b-md" >
                  <h1>Dear {{ $user->name }},</h1>
            </div>
            <div class="content_body">
               <h2>You have been invited to join {{ $user->account_name }} on Admin Panel.
               </h2>
               </br>
               <p>Please click the following link to set your password: </p>
               <a href="{{url('password/reset/'.$user->token.'?email='.urlencode($user->email))}}" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; color: #3869d4;">
               <button class="login-button" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; width: 40%; color: #fff; font-size: 18px; line-height: 42px; text-align: center; border-radius: 4px; margin-bottom: 16px; background: linear-gradient(90deg, #FFAC2F 0%, rgba(212, 31, 50) 100%); border: 0px;">
               Set Password
               </button>
               </a>
               </br>
               <p>Regards,</p>
              
               <p>The XYZ team.</p>
            </div>
         </div>
      </div>
   </body>
</html>