<?php

namespace App\Services;

use App\Models\Service;
use App\Models\User;
use Exception;

class ServiceFactory
{
    /**
     * @param Service $service
     *
     * @throws \Throwable
     */
    public static function getValidator(Service $service)
    {
        $className = "App\\Services\\Validators\\" . $service->id . "Validator";
        throw_if(!class_exists($className), new Exception("Validator class does not exist", 500));

        return new $className();
    }

    /**
     * @param ServiceDTO $serviceDTO
     *
     * @throws \Throwable
     */
    public static function execute(ServiceDTO $serviceDTO): void
    {
        $className = "App\\Services\\Processors\\" . $serviceDTO->getServiceId() . "Processor";
        throw_if(!class_exists($className), new Exception("processor class does not exist", 500));
        /** @var ServiceProcessorBlueprint $processor */
        (new $className())->setServiceDTO($serviceDTO)->execute();
    }

    /**
     * @param Service $service
     * @param mixed $apiResponse
     * 
     * @return bool
     */
    public static function verifyApiResponse(Service $service, mixed $apiResponse): bool
    {
        $className = "App\\Services\\Processors\\" . $service->id . "Processor";
        throw_if(!class_exists($className), new Exception("processor class does not exist", 500));
        return !method_exists($className, 'verifyApiResponse') || (new $className())->verifyApiResponse($apiResponse);
    }
}
