<?php

namespace App\Imports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\ToModel;

class ClientsImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Client([
            'client_name'     => $row[0],
            'org_id'    => $row[1],
            'org_type' => $row[2],
            'sid'    => $row[3],
            'token' => $row[4],
            'oauth_refresh_token' => $row[5],
            'allow_security_flag' => $row[6],
            'allow_AI_flag'    => $row[7],
            'client_id' => $row[8],
            'client_secret'    => $row[9],
            'name_space_sf' => $row[10],
            'client_email'    => $row[11],
            'is_allow_email' => $row[12],
            'is_email_503_allow' => $row[13],
            'is_allow_short_url' => $row[14],
            'short_url_access_token' => $row[15],
            'short_url_created_at' => $row[16],
            'short_url_updated_at' => $row[17],
            'status' => $row[18],
        ]);
    }
}
