<?php

namespace Sztukmistrz\Universe;

use Illuminate\Support\ServiceProvider;
use ReflectionClass;
use Config;
use Illuminate\Foundation\AliasLoader; // if dependences ar present.




/**
*
* ServiceProvider on steroids.
* 14.01.2016
*
*/
class UniverseServiceProvider extends ServiceProvider
{
    /**
    * Path to package src
    * EG: /Library/..../Sztukmistrz/Layouter/src
    */
    private $srcPath;
    /**
    * Path to package 
    * EG: /Library/..../Sztukmistrz/Layouter
    */
    private $packagePath;
    /**
    * Package name - last part of namespace
    * EG: Layouter
    */
    private $packageName;
    /**
    * Provider name package first part of namespace
    * EG: Sztukmistrz
    */
    private $providerName;
    /**
    * Config, Views... Namespace (package resources namespace)  
    * EG: layouter
    */
    private $providerNamespace;
    /**
    * Can be set in config of this package. 
    */
    private $viewsFolderName;
    /**
    * Main config file name.
    * EG: sztukmistrz-layouter.php
    */
    private $mainConfigFileName;
    /**
    * Main publish Folder Name. Lovercase string base on Namespace.
    * EG: vendor/sztukmistrz/layouter
    */
    private $publishFolderName;
 
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // INITIALIZE ------------------------------------
        $this->initialize();
        // INITIALIZE ------------------------------------

        // CONFIGS ---------------------------------------
        // Marge Config From
        $this->mergeConfigFrom($this->packagePath . '/config/' .$this->mainConfigFileName, $this->providerNamespace);
        //$this->setproviderNamespace();
        // publishes
        //dd($this->packagePath. '/config/'.$this->mainConfigFileName);
        $configNames = [
            'mainConfig' => $this->mainConfigFileName,
        ];

        $this->publishConfigs($configNames);
        // CONFIGS ---------------------------------------
        // Get views sub folder name from config
        $this->viewsFolderName = Config::get($this->providerNamespace.'.viewsFolderName');


        // TRANSLATIONS ----------------------------------
        $this->setTranslationNamespace();
        $folderTranslations = [ // all = "*"
            'translations' => 'pl',  // ['pl','en']  
            'translations1' => 'en',
        ];
        $folderTranslations = '*'; // Publish all translations.
        $this->publishTranslations($folderTranslations);
        // TRANSLATIONS ----------------------------------


        // VIEWS -----------------------------------------
        // Establish Views Namespace
        $this->setViewsNamespace();
        
        $folderViews =[
            'mainViews' => $this->viewsFolderName,
        ];
        $this->publishViews($folderViews);
        // VIEWS -----------------------------------------


        // ASSETS ----------------------------------------
        $pathAssets =[
            'mainAssets' => $this->viewsFolderName,
        ];
        $this->publishAssets($pathAssets);
        // ASSETS ----------------------------------------
        

        $this->registerRoutes();

        //dd($this);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        ///$loader = AliasLoader::getInstance();
        
    }




    //===================================================
    //  SETERS
    //===================================================

    /**
    * Publishing by file name (config.php) using key as a TAG
    */
    private function publishConfigs($configNames=false)
    {
        if ($configNames) {
            foreach ($configNames as $tag => $configName) {
                //dd($this->packagePath.'/config/'.$configName);
                $this->publishes([
                    $this->packagePath.'/config/'.$configName => config_path($configName),
                ], $tag);
            }
        }
    }

    /**
    * Publish views 
    * from package views path
    * to public path + $pathView
    * $pathView array | false 
    * in view: @extends('layouter::layouts.backend.base')
    */
    private function publishViews($folderViews=false)
    {
        if ($folderViews) {
            foreach ($folderViews as $tag => $folderView) {
                $this->publishes([
                    $this->srcPath.'/views/'.$folderView => resource_path('views/' . $this->publishFolderName .'/'. $this->viewsFolderName),
                ], $tag);
            }
        }
    }

    /**
    * Publish assets 
    * from package public path + $folderAsset
    * to public path
    * $folderAsset array | false
    */
    private function publishAssets($folderAssets=false)
    {
        //dd($this->publishFolderName);
        if ($folderAssets) {
            //$pathPackagesAssets = 'vendor/'.mb_strtolower($this->providerName).'/'.mb_strtolower($this->packageName);
            //$pathPackagesAssets = 'views/' . $this->publishFolderName;
            foreach ($folderAssets as $tag => $folderAsset) {
                $this->publishes([
                    $this->packagePath.'/public/'.$folderAsset => public_path($this->publishFolderName.'/'.$this->viewsFolderName),
                ], $tag);
            }
        }
    }

    /**
    * Publish translations 
    * from package translations path
    * to public resource/lang/vendor/ + $folderTranslation: "en"
    * $folderTranslations array | "*" | false 
    */
    private function publishTranslations($folderTranslations=false)
    {
        if ($folderTranslations) {
            if (is_array($folderTranslations)) {
                foreach ($folderTranslations as $tag => $folderTranslation) {
                    $this->publishes([
                        $this->srcPath.'/lang/'.$folderTranslation => resource_path('lang/' . $this->publishFolderName.'/'.$folderTranslation),
                    ], $tag);
                }
            }
            if ($folderTranslations == '*') {
                $this->publishes([
                    $this->srcPath.'/lang' => resource_path('lang/' . $this->publishFolderName),
                ], 'langs');
            }
            
        }
    }

    /**
    * Set translation namespace.
    */
    private function setTranslationNamespace()
    {
        $publishedPath = resource_path() . '/lang/' . $this->publishFolderName;

        // Establish Translator Namespace
        if (is_dir($publishedPath)) {
            // The package lang have been published - use those langs.
            $this->loadTranslationsFrom($publishedPath, $this->providerNamespace);

        }else{
            // The package lang
            $this->loadTranslationsFrom($this->srcPath . '/lang', $this->providerNamespace);

        }
        
    }

    /**
    * Set view namespcae
    */
    private function setViewsNamespace()
    {

        $publishedPath = resource_path() . '/views/' . $this->publishFolderName;
        
        // Establish Views Namespace
        if (is_dir($publishedPath)) {
            // The package views have been published - use those views.
              
            $this->loadViewsFrom($publishedPath.'/'.$this->viewsFolderName, $this->providerNamespace);

        } else {
            // The package views have not been published. Use the defaults.

            $this->loadViewsFrom($this->srcPath . '/views/'. $this->viewsFolderName, $this->providerNamespace);

        }
    }

    private function setConfigNamespace()
    {
        $publishedPath = base_path() . '/config/' . $this->mainConfigFileName;
        
        // Establish Views Namespace
        if (file_exists($publishedPath)) {
            // The package views have been published - use those views.
            
            $this->loadViewsFrom($publishedPath.'/'.$this->viewsFolderName, $this->providerNamespace);

        } else {
            // The package views have not been published. Use the defaults.
  
            $this->loadViewsFrom($this->srcPath . '/config/'. $this->viewsFolderName, $this->providerNamespace);

        }
    }

    /**
    * Register routes
    */
    private function registerRoutes()
    {
        if (file_exists($this->srcPath . '/routes.php')) {
            
            if (! $this->app->routesAreCached()) {
                require $this->srcPath . '/routes.php';
            } 

        }
              
    }


    //===================================================
    //  UTILS
    //===================================================

    /**
    * Procdures all default values for provider
    *
    */
    private function initialize()
    {
        // Find path to the package src
        $this->srcPath = $this->getPackagePath();
        // Set path to the package
        $this->packagePath = preg_replace('/\/src$/', '', $this->srcPath);
        // Set package name
        $this->packageName = last($this->getPackageNames());
        // Set provider name
        $this->providerName = $this->getPackageNames()[0];
        // Set conig, views etc namespace
        $this->providerNamespace = mb_strtolower($this->packageName);
        // Set provider name
        $this->mainConfigFileName = mb_strtolower($this->providerName).'-'.mb_strtolower($this->packageName).'.php';

        $this->publishFolderName = 'vendor/'.mb_strtolower($this->providerName).'/'.mb_strtolower($this->packageName);
        
    }

    /**
     * Returning path to the package!
     * @return string 
     */
    private function getPackagePath()
    {
        $filename    = with( new ReflectionClass( '\\' . get_class($this) ) )->getFileName();
        $packagePath     = dirname($filename);
        return $packagePath;
    }

    /**
     * Returning part of namespace by key
     * or namespace convert to array.
     * @return string 
     */
    private function getPackageNames($key=false)
    {
        if ($key) {
            return $this->getPackageNamespaceArray()[$key];
        }else{
            return $this->getPackageNamespaceArray();
        }
        
    }

    /**
     * Returning array from namespace
     * @return array 
     */
    private function getPackageNamespaceArray()
    {
        return explode('\\',__NAMESPACE__);
    }

}