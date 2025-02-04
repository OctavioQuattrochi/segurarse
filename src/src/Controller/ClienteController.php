<?php

namespace App\Controller;

use App\Entity\Cliente;
use App\Repository\ClienteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cliente')]
final class ClienteController extends AbstractController
{
    #[Route(name: 'app_cliente_index', methods: ['GET'])]
    public function index(ClienteRepository $clienteRepository): JsonResponse
    {
        return $this->json(
            $clienteRepository->findAll(),
            200,
            [],
            ['groups' => 'cliente:read'] 
        );
        
    }

    #[Route('/new', name: 'app_cliente_new', methods: ['POST', 'GET'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ClienteRepository $clienteRepository): JsonResponse
    {
        if (!$this->isJsonRequest($request)) {
            return new JsonResponse(['error' => 'Formato incorrecto, usa JSON'], 400);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['nombre'], $data['apellido'], $data['dni'], $data['fechaNacimiento'])) {
            return new JsonResponse(['error' => 'Faltan datos en la solicitud'], 400);
        }

        if ($clienteRepository->findOneBy(['dni' => $data['dni']])) {
            return new JsonResponse(['error' => 'El DNI ya está registrado'], 400);
        }

        $authHeader = base64_encode(str_replace(' ', '', $data['nombre'] . $data['apellido']));
        $validacion = $this->validarClienteExterno($authHeader, $data['nombre'], $data['apellido']);

        if ($validacion !== "OK") {
            return new JsonResponse(['error' => 'El cliente no pasó la validación externa'], 400);
        }

        $cliente = new Cliente();
        $cliente->setNombre($data['nombre'])
            ->setApellido($data['apellido'])
            ->setDni($data['dni'])
            ->setFechaNacimiento(new \DateTime($data['fechaNacimiento']));

        $entityManager->persist($cliente);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Cliente creado correctamente',
            'id' => $cliente->getId(),
        ], 201);
    }

    #[Route('/{id<\d+>}', name: 'app_cliente_show', methods: ['GET'])]
    public function show(int $id, ClienteRepository $clienteRepository): JsonResponse
    {
        $cliente = $clienteRepository->find($id);

        if (!$cliente) {
            return new JsonResponse(['error' => 'Cliente no encontrado'], 404);
        }

        return $this->json($cliente);
    }

    #[Route('/{id}/edit', name: 'app_cliente_edit', methods: ['PUT', 'PATCH'])]
    public function edit(Request $request, int $id, EntityManagerInterface $entityManager, ClienteRepository $clienteRepository): JsonResponse
    {
        if (!$this->isJsonRequest($request)) {
            return new JsonResponse(['error' => 'Formato incorrecto, usa JSON'], 400);
        }

        $cliente = $clienteRepository->find($id);
        if (!$cliente) {
            return new JsonResponse(['error' => 'Cliente no encontrado'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['nombre'], $data['apellido'], $data['dni'], $data['fechaNacimiento'])) {
            return new JsonResponse(['error' => 'Faltan datos en la solicitud'], 400);
        }

        $clienteExistente = $clienteRepository->findOneBy(['dni' => $data['dni']]);
        if ($clienteExistente && $clienteExistente->getId() !== $cliente->getId()) {
            return new JsonResponse(['error' => 'El DNI ya está registrado'], 400);
        }

        $cliente->setNombre($data['nombre'])
            ->setApellido($data['apellido'])
            ->setDni($data['dni'])
            ->setFechaNacimiento(new \DateTime($data['fechaNacimiento']));

        $entityManager->flush();

        return new JsonResponse(['message' => 'Cliente actualizado correctamente'], 200);
    }

    #[Route('/{id}', name: 'app_cliente_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $entityManager, ClienteRepository $clienteRepository): JsonResponse
    {
        $cliente = $clienteRepository->find($id);

        if (!$cliente) {
            return new JsonResponse(['error' => 'Cliente no encontrado'], 404);
        }

        $entityManager->remove($cliente);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Cliente eliminado correctamente'], 200);
    }

    private function validarClienteExterno(string $authHeader, string $nombre, string $apellido): string
    {
        $url = "https://qa.segurarse.com.ar/pruebas/testencrypt";

        $data = json_encode([
            "nombre" => $nombre,
            "apellido" => $apellido
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: $authHeader",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        curl_close($ch);

        $responseData = json_decode($response, true);

        return isset($responseData['result']) && $responseData['result'] === "OK" ? "OK" : "ERROR";
    }

    private function isJsonRequest(Request $request): bool
    {
        return $request->headers->get('Content-Type') === 'application/json';
    }
}
