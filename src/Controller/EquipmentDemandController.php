<?php

namespace App\Controller;

use App\Service\StockInventoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EquipmentDemandController extends AbstractController
{

    /**
     * @Route("/api/stock/{stationId}", name="get_station_equipment_stock", methods={"Get"})
     */
    public function getStationEquipmentStock($stationId, Request $request, StockInventoryService $stockInventoryService)
    {
        try{
            $result = ['status' => false, 'message' => ''];
            $result = $stockInventoryService->getStationStock($stationId, $request);
            if($result['data']){
                $result['status'] = true;
                $result['message'] = 'Success';
               return new JsonResponse($result, Response::HTTP_OK);
            }
        }catch(\Exception $e){
            $result['message'] =$e->getMessage();
            return  new JsonResponse($result, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}