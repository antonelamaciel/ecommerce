<?php
namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class ReceiptController extends AbstractController
{
    #[Route('/comprobante/{reference}', name: 'download_receipt')]
    public function download(string $reference, EntityManagerInterface $em): Response
    {
        $order = $em->getRepository(Order::class)->findOneBy(['reference' => $reference]);
        
        if (!$order) {
            throw $this->createNotFoundException('Comprobante no encontrado.');
        }

        // Seguridad: solo el dueño de la orden o un admin puede descargar el PDF.
        if ($order->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('No tienes permiso para ver este comprobante.');
        }

        if (!$order->getReceiptFilename()) {
            throw $this->createNotFoundException('El comprobante aún no ha sido generado o el pedido es muy antiguo.');
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/receipts/' . $order->getReceiptFilename();
        
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('El archivo PDF del comprobante no existe físicamente en el servidor.');
        }

        $response = new BinaryFileResponse($filePath);
        // DISPOSITION_INLINE lo muestra en el navegador (si el navegador lo soporta).
        // DISPOSITION_ATTACHMENT lo fuerza a descargar.
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE, 
            $order->getReceiptFilename()
        );

        return $response;
    }
}
