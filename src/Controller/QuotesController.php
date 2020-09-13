<?php

namespace App\Controller;

use App\Exception\GetQuoteLimitException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Service\QuoteHandler;

class QuotesController extends AbstractController
{
    private $quoteHandler;

    public function __construct(
        QuoteHandler $quoteHandler
    ) {
        $this->quoteHandler = $quoteHandler;
    }
    
    public function shout(Request $request, $author): JsonResponse
    {
        try {
            $quotes = $this->quoteHandler->findByAuthor($author, $request->query->get('limit', 1));
        } catch (GetQuoteLimitException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse([], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new JsonResponse($quotes, Response::HTTP_OK);
    }
}