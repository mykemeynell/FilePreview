<?php

namespace mykemeynell\FilePreview\FileSystem;

/**
 * Class ApplicationMimeTypes.
 *
 * @package mykemeynell\FilePreview\FileSystem
 */
final class ApplicationMimeTypes
{
    /**
     * Microsoft Office Suite application MIME types.
     *
     * @var array
     */
    public static $MicrosoftOffice = [
        'Word' => [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
        ],
        'Excel' => [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
        ],
        'PowerPoint' => [
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ],
    ];
}
