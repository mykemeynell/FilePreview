# FilePreview

Generate preview images for filetypes.

## Installation with Composer

```composer require mykemeynell/filepreview```

## Usage

```php
$fileName = '/path/to/file.pdf';
$preview = mykemeynell\FilePreview\FilePreview::fromPath($fileName);

echo $preview->preview()->stream();
```

```FilePreview::preview()```

Actions the conversion and prepares headers for output.

```FilePreview::stream()``` 

Sets appropriate headers prepared in ```FilePreview::stream()``` and outputs 
the content.
