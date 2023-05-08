<?php

namespace App\Controller;

use App\Entity\StepsRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Response};
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;

class PdfGeneratorController extends AbstractController
{
    #[Route('/pdf/generator/{id}', name: 'app_pdf_generator')]
    public function index(EntityManagerInterface $em, int $id): Response
    {
        
        $stepRequest = $em->getRepository(StepsRequest::class)->find($id);

        $data = [
            'imageSrc'  => $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/picto/webauto_logo.png'),
            'flag'  => $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/picto/flag.jpg'),
            'name'         => $stepRequest->getName(),
            'mobileNumber' => $stepRequest->getPhone(),
            'email'        => $stepRequest->getEmail(),
            'reference'    => $stepRequest->getReference(),
            'createdAt'    => $stepRequest->getCreatedAt(),
            'price'        => $stepRequest->getPrice(),
            'email'        => $stepRequest->getEmail(),
            'category'     => $stepRequest->getCategory()->getName(),
            'prestation'   => $stepRequest->getPrestaPrice(),
            'agency'       => $stepRequest->getAgency()->getName(),
            'payment'       => $stepRequest->getPayment()->getName(),
        ];

        $html =  $this->renderView('pdf_generator/index.html.twig', $data);
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();

        return new Response (
            $dompdf->stream('resume', ["Attachment" => false]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/pdf']
        );

    }

    private function imageToBase64($path) {
        $path = $path;
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }
}
