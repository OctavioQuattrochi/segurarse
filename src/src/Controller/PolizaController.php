<?php

namespace App\Controller;

use App\Entity\Poliza;
use App\Entity\Cliente;
use App\Repository\PolizaRepository;
use App\Repository\ClienteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/poliza')]
final class PolizaController extends AbstractController
{
    #[Route(name: 'app_poliza_index', methods: ['GET'])]
    public function index(PolizaRepository $polizaRepository): JsonResponse
    {
        return $this->json($polizaRepository->findAll(), 200, [], ['groups' => 'poliza:read']);
    }

    #[Route('/new', name: 'app_poliza_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ClienteRepository $clienteRepository, PolizaRepository $polizaRepository): JsonResponse
    {
        if (!$this->isJsonRequest($request)) {
            return new JsonResponse(['error' => 'Formato incorrecto, usa JSON'], 400);
        }
    
        $data = json_decode($request->getContent(), true);
    
        if (!$data || !isset($data['cliente_id'], $data['auto'], $data['costo'], $data['fechaVigencia'])) {
            return new JsonResponse(['error' => 'Faltan datos en la solicitud'], 400);
        }
        
        $cliente = $clienteRepository->find($data['cliente_id']);
        if (!$cliente) {
            return new JsonResponse(['error' => 'El cliente no existe'], 404);
        }

        if ($polizaRepository->findOneBy(['cliente' => $cliente])) {
            return new JsonResponse(['error' => 'El cliente ya tiene una póliza asociada'], 400);
        }
    
        $poliza = new Poliza();
        $poliza->setCliente($cliente)
               ->setAuto($data['auto'])
               ->setCosto($data['costo'])
               ->setFechaVigencia(new \DateTime($data['fechaVigencia']));
    
        $entityManager->persist($poliza);
        $entityManager->flush();
    
        return new JsonResponse(['message' => 'Póliza creada correctamente'], 201);
    }

    #[Route('/{id}', name: 'app_poliza_show', methods: ['GET'])]
    public function show(Poliza $poliza): JsonResponse
    {
        return $this->json($poliza, 200, [], ['groups' => 'poliza:read']);
    }

    #[Route('/{id}/edit', name: 'app_poliza_edit', methods: ['PUT'])]
    public function edit(Request $request, Poliza $poliza, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isJsonRequest($request)) {
            return new JsonResponse(['error' => 'Formato incorrecto, usa JSON'], 400);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['auto'], $data['costo'], $data['fechaVigencia'])) {
            return new JsonResponse(['error' => 'Faltan datos en la solicitud'], 400);
        }

        $poliza->setAuto($data['auto'])
               ->setCosto($data['costo'])
               ->setFechaVigencia(new \DateTime($data['fechaVigencia']));

        $entityManager->flush();

        return new JsonResponse(['message' => 'Póliza actualizada correctamente'], 200);
    }

    #[Route('/{id}', name: 'app_poliza_delete', methods: ['DELETE'])]
    public function delete(int $id, PolizaRepository $polizaRepository, Poliza $poliza, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$polizaRepository->find($id)) {
            return new JsonResponse(['error' => 'La póliza no existe.'], 404);
        }

        $entityManager->remove($poliza);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Póliza eliminada correctamente'], 200);
    }

    /**
     * Verifica si la solicitud es JSON.
     */
    private function isJsonRequest(Request $request): bool
    {
        return str_contains($request->headers->get('Content-Type'), 'application/json');
    }
}
