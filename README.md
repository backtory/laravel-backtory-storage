# Laravel Backtory Storage Driver 

A Backtory Storage filesystem for Laravel.

This package is a wrapper bridging [backtory/storage-php](https://github.com/backtory/storage-php) into Laravel as an available storage disk.

## Installation

```bash
composer require backtory/laravel-backtory-storage
```

Register the service provider in app.php (only in versions < 5.5)
```php
'providers' => [
    // ...
    Backtory\Storage\Laravel\BacktoryStorageServiceProvider::class,
]
```

Add a new disk to your `filesystems.php` config

```php
'backtory' => [
    'driver' => 'backtory',
    'X-Backtory-Authentication-Id' => '',
    'X-Backtory-Authentication-Key' => '',
    'X-Backtory-Object-Storage-Id' => '',
    'domain' => '' // [optional]
],
```

## Usage

```php
$disk = Storage::disk('backtory');

// create a file
$disk->put('avatars/file.jpg', $fileContents);

// check if a file exists
$exists = $disk->exists('file.jpg');

// get file modification date
$time = $disk->lastModified('file1.jpg');

// copy a file
$disk->copy('old/file1.jpg', 'newLocaltion');

// move a file
$disk->move('old/file1.jpg', 'newLocation');

// get url to file
$url = $disk->url('folder/my_file.txt');

// See https://laravel.com/docs/5.5/filesystem for full list of available functionality
```