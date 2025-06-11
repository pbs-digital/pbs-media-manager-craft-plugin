<?php
/**
 * Media Manager
 *
 * @package       Media Manager
 * @author        PBS Digital
 * @link          https://github.com/pbs-digital/pbs-media-manager-craft-plugin
 */

namespace pbsdigital\mediamanager\assetbundles\entriescpsection;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class EntriesCPSectionAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    public function init()
    {
        $this->sourcePath = '@pbsdigital/mediamanager/assetbundles/entriescpsection/dist';
        $this->depends    = [ CpAsset::class ];
        $this->js         = [ 'js/Entries.js' ];
        $this->css        = [ 'css/Entries.css' ];

        parent::init();
    }
}
