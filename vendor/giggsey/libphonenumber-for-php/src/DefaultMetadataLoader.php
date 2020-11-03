<?php

namespace MolliePrefix\libphonenumber;

class DefaultMetadataLoader implements \MolliePrefix\libphonenumber\MetadataLoaderInterface
{
    public function loadMetadata($metadataFileName)
    {
        return include $metadataFileName;
    }
}
