Cloud Assets Module
===================

Swift CloudFiles Driver
---------------------------

CloudAssets module allows you to host all or part of the assets folder on a cloud storage container (CDN).
You can find more details about how it works here: <https://github.com/markguinn/silverstripe-cloudassets>

This driver gives you the bucket type SwiftBucket for connecting to CloudFiles.

This module can happily co-exist with other bucket driver modules (which don't exist at the time of this writing).


Requirements
------------
- Silverstripe 3.1+
- Cloud Assets module
- php-opencloud/openstack

Best way to install by far is `composer require smarcet/silverstripe-cloudassets-swift`.


Example
-------
Assuming you have a CloudFiles container called site-uploads:

*mysite/_config/cloudassets.yml:*

````yaml

---
name: assetsconfig
---
CloudAssets:
  map:
    'assets':
      Type: SwiftBucket
      BaseURL: 'http://yourcdnbaseurl.com/'
      Container: site-uploads
      Region: Region Name
      Username: yourlogin
      UserDomainId: user domain id (default)
      ApiKey: yourkey
      ProjectName: your project name
      AuthURL: keystone base url 
      ProjectDomainId: project domain id (default)
      LocalCopy: false     
````

OR using application credentials

* https://docs.openstack.org/keystone/rocky/user/application_credentials.html


```yaml

---
name: assetsconfig
---
CloudAssets:
  map:
    'assets':
      Type: SwiftBucket
      BaseURL: 'http://yourcdnbaseurl.com/'
      Container: site-uploads
      Region: Region Name
      ApplicationCredentialId: application credential id
      ApplicationCredentialSecret: application credential secret
      ProjectName: your project name
      AuthURL: keystone base url 
      LocalCopy: false     
````
