<?php
/**
 * Media Manager
 *
 * @package       Media Manager
 * @author        PBS Digital
 * @link          https://github.com/pbs-digital/pbs-media-manager-craft-plugin
 */

namespace pbsdigital\mediamanager\assetbundles\synchronizecpsection;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SynchronizeCPSectionAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    public function init()
    {
        $this->sourcePath = '@pbsdigital/mediamanager/assetbundles/synchronizecpsection/dist';
        $this->depends    = [ CpAsset::class ];
        $this->js         = [ 'js/Synchronize.js' ];
        $this->css        = [ 'css/Synchronize.css' ];

        parent::init();
    }
}
