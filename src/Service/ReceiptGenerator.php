<?php

namespace App\Service;

use App\Entity\Order;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

class ReceiptGenerator
{
    private $twig;
    private $projectDir;
    private $personalizeRepository;

    public function __construct(Environment $twig, KernelInterface $kernel, \App\Repository\PersonalizeRepository $personalizeRepository)
    {
        $this->twig = $twig;
        $this->projectDir = $kernel->getProjectDir();
        $this->personalizeRepository = $personalizeRepository;
    }

    public function generate(Order $order): string
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);

        // Render HTML
        $html = $this->twig->render('pdf/receipt.html.twig', [
            'order' => $order,
            'personalize' => $this->personalizeRepository->findOneBy([])
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();
        
        // Define path
        $publicPath = $this->projectDir . '/public/uploads/receipts';
        if (!is_dir($publicPath)) {
            mkdir($publicPath, 0777, true);
        }

        $filename = 'comprobante_' . $order->getReference() . '.pdf';
        file_put_contents($publicPath . '/' . $filename, $output);

        return $filename;
    }
}
