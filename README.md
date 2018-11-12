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
```
---
name: assetsconfig
---
CloudAssets:
  map:
    'assets/Uploads':
      Type: SwiftBucket
      BaseURL: 'http://yourcdnbaseurl.com/'
      Container: site-uploads
      Region: ORD
      Username: yourlogin
      ApiKey: yourkey
      ProjectId: yourProjectId
      LocalCopy: false     
```
