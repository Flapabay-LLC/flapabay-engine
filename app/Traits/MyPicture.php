<?php

namespace App\Traits;

use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Exception;

trait MyPicture
{
    public function uploadImagesToWasabi(Request $request)
    {
        $imagePaths = [];

        if ($request->hasFile('images')) {
            $s3Client = $this->initializeWasabiClient();

            foreach ($request->file('images') as $image) {
                if ($image->isValid()) {
                    $fileName = time() . '_' . $image->getClientOriginalName();

                    $result = $s3Client->putObject([
                        'Bucket'     => env('WASABI_BUCKET'),
                        'Key'        => 'stays/' . $fileName,
                        'SourceFile' => $image->getPathname(),
                    ]);

                    if (isset($result['ObjectURL'])) {
                        $imagePaths[] = $result['ObjectURL'];
                    } else {
                        throw new Exception('Failed to upload image to Wasabi.');
                    }
                }
            }
        }

        return $imagePaths;
    }

    private function initializeWasabiClient()
    {
        return new S3Client([
            'region'      => 'us-west-1',
            'version'     => 'latest',
            'endpoint'    => 'https://s3.us-west-1.wasabisys.com',
            'credentials' => [
                'key'    => 'HJG2GQM9QGBE4K6JCO2S',
                'secret' => 'HkHlBtvEszE2Uh18ZWgCw3t2BXd7CBPy75mMWEnD',
            ],
        ]);
        // return new S3Client([
        //     'region'      => env('WASABI_REGION'),
        //     'version'     => 'latest',
        //     'endpoint'    => env('WASABI_ENDPOINT'),
        //     'credentials' => [
        //         'key'    => env('WASABI_ACCESS_KEY'),
        //         'secret' => env('WASABI_SECRET_KEY'),
        //     ],
        // ]);
    }
}
