<?php

namespace CTMovie;

use CTMovie\Model\MovieService;
use CTMovie\Objects\AssetManager;
use Illuminate\Filesystem\Filesystem;
use CTMovie\Controller\GeneralSetting;
use CTMovie\Controller\Report;
use CTMovie\Model\DatabaseService;
use CTMovie\Model\SchedulingService;
use CTMovie\Model\LauCDN\Connection;
use CTMovie\Model\Service\OauthGDrive;
use CTMovie\Model\RegisterCustomApi;

/**
 * Class ObjectFactory
 * @package CTMovie
 */
class ObjectFactory
{
    /**
     * @var ObjectFactory
     */
    private static $instance;

    private static $fs;

    private static $movieService;

    private static $databaseService;

    private static $schedulingService;

    private static $generalSettingsController;

    private static $lauConnection;

    private static $report;

    private static $gdriveService;

    private static $registerApi;

    /**
     * ObjectFactory constructor.
     */
    public function __construct()
    {
        ObjectFactory::movieService();
        ObjectFactory::generalSettingsController();
        ObjectFactory::databaseService();
        ObjectFactory::schedulingService();
        ObjectFactory::reportController();
        ObjectFactory::GDriveService();
        ObjectFactory::registerCustomApi();
    }

    /** @return ObjectFactory */
    public static function getInstance(): ObjectFactory
    {
        return static::getClassInstance(ObjectFactory::class, static::$instance);
    }

    /** @return MovieService */
    public static function movieService(): MovieService
    {
        return static::getClassInstance(MovieService::class, static::$movieService);
    }

    /** @return GeneralSetting */
    public static function generalSettingsController() {
        return static::getClassInstance(GeneralSetting::class, static::$generalSettingsController);
    }

    /** @return RegisterCustomApi */
    public static function registerCustomApi() {
        return static::getClassInstance(RegisterCustomApi::class, static::$registerApi);
    }

    /** @return Report */
    public static function reportController() {
        return static::getClassInstance(Report::class, static::$report);
    }

    /** @return DatabaseService */
    public static function databaseService() {
        return static::getClassInstance(DatabaseService::class, static::$databaseService);
    }

    /** @return DatabaseService */
    public static function GDriveService() {
        return static::getClassInstance(OauthGDrive::class, static::$gdriveService);
    }

    /** @return Connection */
    public static function lauConnection() {
        return static::getClassInstance(Connection::class, static::$lauConnection);
    }

    /** @return SchedulingService */
    public static function schedulingService() {
        return static::getClassInstance(SchedulingService::class, static::$schedulingService);
    }

    /** @return AssetManager */
    public static function assetManager() {
        return AssetManager::getInstance();
    }

    /**
     * Create or get instance of a class. A wrapper method to work with singletons. You need to import the class
     * with "use" before calling this method.
     *
     * @param string $className Name of the class. E.g. MyClass::class
     * @param mixed $staticVar A static variable that will store the instance of the class
     * @return mixed                    A singleton of the class
     */
    private static function getClassInstance(string $className, &$staticVar) {
        if(!$staticVar) {
            $staticVar = new $className();
        }

        return $staticVar;
    }

    /** @return Filesystem */
    public static function fileSystem() {
        return static::getClassInstance(Filesystem::class, static::$fs);
    }
}