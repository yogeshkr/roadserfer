<?php

namespace App\Controller;

use App\Service\OrderService;
use App\Service\StockInventoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{

    /**
     * @Route("/api/order", name="add_order", methods={"POST"})
     */
    public function add(Request $request, OrderService $orderService)
    {
        try{
            $request = json_decode($request->getContent(), true);
            $user = $this->getUser();
            $result = ['status' => false, 'message' => ''];
            $result = $orderService->createOrder($user, $request);
            if($result){
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