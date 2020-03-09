<?php

namespace Survos\LandingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends AbstractController
{

    public function jsonResponse($data, Request $request = null, $format='html')
    {
        if ($request && $request->isXmlHttpRequest()) {
            $format = 'json';
        }
        return $format === 'json'
            ? new JsonResponse($data)
            : new Response(sprintf('<html lang="en"><body><pre>%s</pre></body></html>', json_encode($data, JSON_UNESCAPED_SLASHES + JSON_PRETTY_PRINT )) );
    }

    public function handleNext(FormInterface $form, string $defaultRoute ): string {
        $nextRoute = $defaultRoute;
        if ($form->has('_next_route')) {
            $nextRoute = $form->get('_next_route')->getData();
        }
        return $nextRoute;
    }

}
