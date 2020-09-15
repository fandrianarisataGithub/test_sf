<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\User;
use App\Entity\Client;
use App\Form\UserType;
use App\Entity\Pointage;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use App\Repository\PointageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
// Include Dompdf required namespaces
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PageController extends AbstractController
{
    
    /**
     * @Route("/profile/client_present", name="client_present")
     */
    public function client_present(ClientRepository $repoClient)
    {
        $user = new User();
        $items = $repoClient->countPresent('présent');
        //dd($items);
        return $this->render('page/client_present.html.twig', [
            'items' => $items,
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'impaye' => $this->count_impaye($repoClient),
        ]);
    }
    /**
     * @Route("/profile/imprimer/client_present", name="imprimer_client_present")
     */
    public function imprimer_client_present(ClientRepository $repoClient)
    {
        $user = new User();
        $items = $repoClient->countPresent('présent');
        // dompdf
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        
        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('page/listePresentImpr.html.twig', [
            'items' => $items,
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'impaye' => $this->count_impaye($repoClient),
        ]);
        
        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        
        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => true
        ]);
    }

    /**
     * @Route("/profile/imprimer/client_impaye", name="imprimer_client_impaye")
     */
    public function imprimer_client_impaye(ClientRepository $repoClient)
    {
        $user = new User();
        $items = $repoClient->countPresent('impaye');
        // dompdf
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('page/listeImpayeImpr.html.twig', [
            'items' => $items,
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'impaye' => $this->count_impaye($repoClient),
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => true
        ]);
    }

    /**
     * @Route("/profile/imprimer/client_suspendu", name="imprimer_client_suspendu")
     */
    public function imprimer_client_suspendu(ClientRepository $repoClient)
    {
        $user = new User();
        $items = $repoClient->countPresent('suspendu');
        // dompdf
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('page/listeSuspenduImpr.html.twig', [
            'items' => $items,
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'impaye' => $this->count_impaye($repoClient),
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => true
        ]);
    }


    /**
     * @Route("/admin/client_paye", name="client_impaye")
     */
    public function client_paye(ClientRepository $repoClient)
    { 
        $user = new User();
        $items = $repoClient->countPresent('impayé');
        return $this->render('page/client_paye.html.twig', [
            'items' => $items,
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'impaye' => $this->count_impaye($repoClient),
        ]);
    }
    /**
     * @Route("/admin/client_suspendu", name="client_suspendu")
     */
    public function client_suspendu(ClientRepository $repoClient)
    {
        $user = new User();
        $items = $repoClient->countPresent('suspendu'); 
        return $this->render('page/client_suspendu.html.twig', [
            'items' => $items,
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'impaye' => $this->count_impaye($repoClient),
        ]);
    }

    /**
     * @Route("/profile/single_page/{id_client}", name="single_page")
     */
    public function single_page($id_client, ClientRepository $repoClient, PointageRepository $repoPointage)
    {   
        $client = new Client();
        $client = $repoClient->find($id_client);
        $pointage = new Pointage();
        $montant = $client->getMontant();
        $nbrMois = $client->getNbrVersement();
        $montant_mensuel = $client->getMontantMensuel();
        $pointage = $repoPointage->findByClient($client);
        $nbr_p_effectue = count($pointage);
        //dd($nbr_p_effectue);
        // nombre de mois restant

        $nbr_mois_restant = $nbrMois - $nbr_p_effectue;
        
        // montant payé
        $montant_paye = $montant_mensuel * $nbr_p_effectue;

        // montant restant
        $montant_restant = $montant - $montant_paye;
        
        return $this->render('page/single_page.html.twig', [
            'item' => $client,
            'montant_paye' => $montant_paye,
            'montant_restant' => $montant_restant,
            'nbr_p_effectue' => $nbr_p_effectue,
            'nbr_paiement_total' => $nbrMois,
            'nbr_mois_restant' => $nbr_mois_restant,
            "nbr_pointage" => $nbr_p_effectue,
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'impaye' => $this->count_impaye($repoClient),
        ]);
    }
    /**
     * @Route("/admin/pointage", name="pointage")
     */
    public function pointage(ClientRepository $repoClient)
    {
        $user = new User();
        $items = $repoClient->findAll();
        return $this->render('page/pointage.html.twig', [
            'items' => $items,
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'impaye' => $this->count_impaye($repoClient),
        ]);
    }
   
    public function count_present(ClientRepository $repoClient)
    {
        $tabPresent = $repoClient->countPresent('présent');
        $n = count($tabPresent);
        //dd($n);
        return $n;
        
    }
    public function count_suspendu(ClientRepository $repoClient)
    {
        $tabPresent = $repoClient->countPresent('suspendu');
        $n = count($tabPresent);
        //dd($n);
        return $n;
    }
    public function count_impaye(ClientRepository $repoClient)
    {
        $tabPresent = $repoClient->countPresent('impayé');
        $n = count($tabPresent);
        //dd($n);
        return $n;
    }
    
}
