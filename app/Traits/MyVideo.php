<?php

namespace App\Traits;

use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Exception;

trait MyVideo
{
    public function uploadVideosToWasabi(Request $request)
    {
        $videoPaths = [];

        if ($request->hasFile('videos')) {
            $s3Client = $this->initializeWasabiVClient();

            foreach ($request->file('videos') as $video) {
                if ($video->isValid()) {
                    $fileName = time() . '_' . $video->getClientOriginalName();

                    $result = $s3Client->putObject([
                        'Bucket'     => env('WASABI_BUCKET'),
                        'Key'        => 'stays/' . $fileName,
                        'SourceFile' => $video->getPathname(),
                    ]);

                    if (isset($result['ObjectURL'])) {
                        $videoPaths[] = $result['ObjectURL'];
                    } else {
                        throw new Exception('Failed to upload video to Wasabi.');
                    }
                }
            }
        }

        return $videoPaths;
    }

    private function initializeWasabiVClient()
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
    }
}
