<?php

use Outils\Utilitaires;


$mois = Utilitaires::getMois(date('d/m/Y'));
$numAnnee = substr($mois, 0, 4);
$numMois = substr($mois, 4, 2);
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
//$idVisiteur = $_SESSION['idVisiteur'];
switch ($action) {
    case 'afficherVisiteurs':
        $visiteurs = $pdo->getAllVisiteur();
        include PATH_VIEWS .'v_listeVisiteursComptable.php';
        break;
    case 'selectionnerMois':
        if (isset($_POST['visiteur'])) {
            $visiteurLogin = $_POST['visiteur'];
            $visiteurId = $pdo->getVisiteurId($visiteurLogin);

            if ($visiteurId) {
                $visiteurMonths = $pdo->getMoisCloturesVisiteur($visiteurId);
                include PATH_VIEWS .'v_listeMoisComptable.php';
            } else {
                $messageErreur = "Visiteur introuvable";
                include PATH_VIEWS .'v_erreurs.php';
            }
        }
        break;
    case 'afficherFicheFrais':
        if (isset($_POST['visiteur']) && isset($_POST['mois'])) {
            $visiteurLogin = $_POST['visiteur'];
            $mois = $_POST['mois'];
            $visiteurId = $pdo->getVisiteurId($visiteurLogin);

            if ($visiteurId) {
                $lesFraisForfait = $pdo->getLesFraisForfait($visiteurId, $mois);
                $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($visiteurId, $mois);
                include PATH_VIEWS .'v_etatFraisComptable.php';
            } else {
                $messageErreur = "Données invalides.";
                include PATH_VIEWS .'v_erreurs.php';
            }
        }
        break;
    case 'corrigerFrais':
        if (isset($_POST['visiteur'], $_POST['mois'], $_POST['lesFrais'])) {
            $visiteurId = $pdo->getVisiteurId($_POST['visiteur']);
            $mois = $_POST['mois'];
            $frais = $_POST['lesFrais'];

            $pdo->setLesFraisForfait($visiteurId, $mois, $frais);

            $lesFraisForfait = $pdo->getLesFraisForfait($visiteurId, $mois);
            $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($visiteurId, $mois);

            include PATH_VIEWS .'v_etatFraisComptable.php';
        }
        break;
    case 'corrigerFraisHorsForfait':
        if (isset($_POST['idFrais'], $_POST['visiteur'], $_POST['mois'], $_POST['dateFrais'], $_POST['libelleFrais'], $_POST['montantFrais'])) {
            $idFrais = $_POST['idFrais'];
            $visiteurLogin = $_POST['visiteur'];
            $visiteurId = $pdo->getVisiteurId($visiteurLogin);
            $mois = $_POST['mois'];
            $dateFrais = $_POST['dateFrais'];
            $libelleFrais = $_POST['libelleFrais'];
            $montantFrais = $_POST['montantFrais'];

            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateFrais)) {
                $dateObj = DateTime::createFromFormat('d/m/Y', $dateFrais);
                $dateFrais = $dateObj->format('Y-m-d');
            }

            $pdo->updateFraisHorsForfait($idFrais, $visiteurId, $mois, $dateFrais, $libelleFrais, $montantFrais);

            $lesFraisForfait = $pdo->getLesFraisForfait($visiteurId, $mois);
            $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($visiteurId, $mois);

            include PATH_VIEWS . 'v_etatFraisComptable.php';
        }
        break;

    case 'reinitialiserFrais':
        if (isset($_POST['visiteur'], $_POST['mois'], $_POST['idFrais'])) {
            $visiteurId = $pdo->getVisiteurId($_POST['visiteur']);
            $mois = $_POST['mois'];
            $idFrais = $_POST['idFrais'];

            $pdo->resetUnFraisHorsForfait($visiteurId, $mois, $idFrais);

            $lesFraisForfait = $pdo->getLesFraisForfait($visiteurId, $mois);
            $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($visiteurId, $mois);

            include PATH_VIEWS .'v_etatFraisComptable.php';
        }
        break;
    case 'refuserFrais':
        if (isset($_POST['visiteur'], $_POST['mois'], $_POST['idFrais'])) {
            $visiteurLogin = $_POST['visiteur'];
            $mois = $_POST['mois'];
            $idFrais = $_POST['idFrais'];

            $visiteurId = $pdo->getVisiteurId($visiteurLogin);

            if ($visiteurId) {
                $pdo->refuserFraisHorsForfait($visiteurId, $mois, $idFrais);

                $lesFraisForfait = $pdo->getLesFraisForfait($visiteurId, $mois);
                $lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($visiteurId, $mois);

                include PATH_VIEWS . 'v_etatFraisComptable.php';
            } else {
                $messageErreur = "Visiteur invalide.";
                include PATH_VIEWS . 'v_erreurs.php';
            }
        }
        break;
    case 'genererPDF':
        if (isset($_POST['visiteur'], $_POST['mois'])) {
            $visiteurId = $pdo->getVisiteurId($_POST['visiteur']);
            $mois = $_POST['mois'];

            if ($visiteurId) {
                $pdo->generatePdf($visiteurId, $mois);
            } else {
                $messageErreur = "Visiteur introuvable ou mois invalide.";
                include PATH_VIEWS . 'v_erreurs.php';
            }
        }
        break;
    default:
        include PATH_VIEWS .'v_accueil.php';
        break;
}

