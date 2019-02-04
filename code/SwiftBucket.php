<?php
/**
 * Copyright 2018 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use OpenStack\ObjectStore\v1\Models\StorageObject;
use OpenStack\OpenStack;
use GuzzleHttp\Psr7\Stream;
/**
 * Class SwiftBucket
 */
final class SwiftBucket extends CloudBucket
{

    const CONTAINER         = 'Container';
    const REGION            = 'Region';
    const USERNAME          = 'Username';
    const API_KEY           = 'ApiKey';
    const PROJECT_NAME      = 'ProjectName';
    const USER_DOMAIN_ID    = 'UserDomainId';
    const PROJECT_DOMAIN_ID = 'ProjectDomainId';
    const AUTH_URL          = 'AuthURL';
    const APP_CRED_ID       = 'ApplicationCredentialId';
    const APP_CRED_SECRET   = 'ApplicationCredentialSecret';

    /**
     * @var StorageObject
     */
    protected $container;

    protected $containerName;

    /**
     * @param string $path
     * @param array  $cfg
     * @throws Exception
     */
    public function __construct($path, array $cfg=[])
    {
        parent::__construct($path, $cfg);

        if (empty($cfg[self::CONTAINER])) {
            throw new Exception('SwiftBucket: missing configuration key - ' . self::CONTAINER);
        }

        if (empty($cfg[self::REGION])) {
            throw new Exception('SwiftBucket: missing configuration key - ' . self::REGION);
        }

        if (empty($cfg[self::PROJECT_NAME])) {
            throw new Exception('SwiftBucket: missing configuration key - ' . self::PROJECT_NAME);
        }

        if (empty($cfg[self::AUTH_URL])) {
            throw new Exception('SwiftBucket: missing configuration key - ' . self::AUTH_URL);
        }

        $userPassword = true;
        $applicationCredentials = true;

        if (empty($cfg[self::USERNAME])) {
            $userPassword = false;
        }

        if (empty($cfg[self::API_KEY])) {
            $userPassword = false;
        }

        if (empty($cfg[self::APP_CRED_ID])) {
            $applicationCredentials = false;
        }

        if (empty($cfg[self::APP_CRED_SECRET])) {
            $applicationCredentials = false;
        }

        if(!$userPassword && !$applicationCredentials){
            throw new Exception('SwiftBucket: You must provided some credentials: User Credentials  OR Application Credentials');
        }

        $this->containerName = $this->config[self::CONTAINER];
    }


    /**
     * @return \OpenStack\ObjectStore\v1\Models\Container|StorageObject
     */
    protected function getContainer()
    {
        if (!isset($this->container)) {
            $configOptions = [
                'authUrl' => $this->config[self::AUTH_URL],
                'region' => $this->config[self::REGION],
            ];

            if(isset($this->config[self::USERNAME])){
                $configOptions['user'] = [
                    'name' => $this->config[self::USERNAME],
                    'password' => $this->config[self::API_KEY],
                    'domain' => ['id' => isset($this->config[self::USER_DOMAIN_ID]) ? $this->config[self::USER_DOMAIN_ID] : 'default']
                ];

                $configOptions['scope' ] =  [
                    'project' => [
                        'name' => $this->config[self::PROJECT_NAME],
                        'domain' => ['id' => isset($this->config[self::PROJECT_DOMAIN_ID]) ? $this->config[self::PROJECT_DOMAIN_ID] : 'default']
                    ],
                ];
            }

            if(isset($this->config[self::APP_CRED_ID])){
                $configOptions['application_credential'] = [
                    'id' => $this->config[self::APP_CRED_ID],
                    'secret' => $this->config[self::APP_CRED_SECRET],
                ];
            }

            $openstack = new OpenStack($configOptions);

            $this->container = $openstack->objectStoreV1()->getContainer($this->containerName);
        }

        return $this->container;
    }


    /**
     * @param File $f
     */
    public function put(File $f)
    {

        $fp = fopen($f->getFullPath(), 'r');
        if (!$fp) {
            throw new Exception("Unable to open file: " . $f->getFilename());
        }

        $options = [
            'name'   =>  $this->getRelativeLinkFor($f),
            'stream' =>  new Stream($fp)
        ];

        $res = $this->getContainer()->createObject($options);
        return  $res;
    }

    /**
     * NOTE: This method must handle string filenames as well
     * for the purpose of deleting cached resampled images.
     * @param File|string $f
     */
    public function delete($f)
    {
        if($f instanceof File)
            $f = $f->getFilename();
        $this->getContainer()->getObject($f)->delete();
    }

    /**
     * @param File $f
     * @param string $beforeName - contents of the Filename property (i.e. relative to site root)
     * @param string $afterName - contents of the Filename property (i.e. relative to site root)
     */
    public function rename(File $f, $beforeName, $afterName)
    {
        $obj = $this->getFileObjectFor($beforeName);
        if(is_null($obj))
            throw new Exception("obj is null!!");
        $obj->copy(['destination' => $this->containerName . '/' . $this->getRelativeLinkFor($afterName)]);
        $obj->delete();
    }

    /**
     * @param File $f
     * @return string
     */
    public function getContents(File $f)
    {
        $obj = $this->getFileObjectFor($f);
        if(is_null($obj))
            return null;
        $stream = $obj->download();
        if(is_null($stream))
            return null;
        return $stream->getContents();
    }

    /**
     * @param File|string $f
     * @return StorageObject
     */
    protected function getFileObjectFor($f)
    {
        $name = $this->getRelativeLinkFor($f);
        return $this->getContainer()->getObject($name);
    }

    /**
     * @param File|string $f
     * @return int
     */
    public function getFileSize($f)
    {
        $obj = $this->getFileObjectFor($f);
        if(is_null($obj))
            return null;
        $obj->getMetadata();
        return $obj->contentLength;
    }

    /**
     * @param File|string $f
     * @return \GuzzleHttp\Psr7\Uri|string
     */
    public function getPublicURLFor($f)
    {
        $obj = $this->getFileObjectFor($f);
        if(is_null($obj))
            return null;
        return $obj->getPublicUri();
    }
}