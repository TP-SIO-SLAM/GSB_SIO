<?php

/**
 * Classe d'accès aux données.
 *
 * PHP Version 8
 *
 * @category  PPE
 * @package   GSB
 * @author    Cheri Bibi - Réseau CERTA <contact@reseaucerta.org>
 * @author    José GIL - CNED <jgil@ac-nice.fr>
 * @copyright 2017 Réseau CERTA
 * @license   Réseau CERTA
 * @version   GIT: <0>
 * @link      http://www.php.net/manual/fr/book.pdo.php PHP Data Objects sur php.net
 */

/**
 * Classe d'accès aux données.
 *
 * Utilise les services de la classe PDO
 * pour l'application GSB
 * Les attributs sont tous statiques,
 * les 4 premiers pour la connexion
 * $connexion de type PDO
 * $instance qui contiendra l'unique instance de la classe
 *
 * PHP Version 8
 *
 * @category  PPE
 * @package   GSB
 * @author    Cheri Bibi - Réseau CERTA <contact@reseaucerta.org>
 * @author    José GIL <jgil@ac-nice.fr>
 * @copyright 2017 Réseau CERTA
 * @license   Réseau CERTA
 * @version   Release: 1.0
 * @link      http://www.php.net/manual/fr/book.pdo.php PHP Data Objects sur php.net
 */

namespace Modeles;

use FPDF;
use PDO;
use Outils\Utilitaires;

require '../config/bdd.php';
require '../libs/fpdf/fpdf.php';
//require_once '../vendor/autoload.php';


class PdoGsb
{
    protected $connexion;
    private static $instance = null;

    /**
     * Constructeur privé, crée l'instance de PDO qui sera sollicitée
     * pour toutes les méthodes de la classe
     */
    private function __construct()
    {
        $this->connexion = new PDO(DB_DSN, DB_USER, DB_PWD);
        $this->connexion->query('SET CHARACTER SET utf8');
    }

    /**
     * Méthode destructeur appelée dès qu'il n'y a plus de référence sur un
     * objet donné, ou dans n'importe quel ordre pendant la séquence d'arrêt.
     */
    public function __destruct()
    {
        $this->connexion = null;
    }

    /**
     * Fonction statique qui crée l'unique instance de la classe
     * Appel : $instancePdoGsb = PdoGsb::getPdoGsb();
     *
     * @return l'unique objet de la classe PdoGsb
     */
    public static function getPdoGsb(): PdoGsb
    {
        if (self::$instance == null) {
            self::$instance = new PdoGsb();
        }
        return self::$instance;
    }

    /**
     * Retourne les informations d'un visiteur
     *
     * @param String $login Login du visiteur
     * @param String $mdp   Mot de passe du visiteur
     *
     * @return l'id, le nom et le prénom sous la forme d'un tableau associatif
     */
    public function getInfosVisiteur($login): array
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT visiteur.id AS id, visiteur.nom AS nom, '
            . 'visiteur.prenom AS prenom '
            . 'FROM visiteur '
            . 'WHERE visiteur.login = :unLogin '
        );
        $requetePrepare->bindParam(':unLogin', $login, PDO::PARAM_STR);
        $requetePrepare->execute();

        $result = $requetePrepare->fetch();
        return $result ? $result : [];
    }


    /**
     * Retourne les informations d'un comptable
     * 
     * @param String $login login du visiteur
     * @param String $mdp Mot de passe du visiteur
     * 
     * @return l'id, le nom et le prénom sous la forme d'un tableau associatif
     */
    public function getInfosComptable($login): array
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT comptable.id AS id, comptable.nom AS nom, '
            . 'comptable.prenom AS prenom '
            . 'FROM comptable '
            . 'WHERE comptable.login = :unLogin'
        );
        $requetePrepare->bindParam(':unLogin', $login, PDO::PARAM_STR);
        $requetePrepare->execute();

        $result = $requetePrepare->fetch();
        return $result ? $result : [];
    }

    /**
     * Met à jour les mots de passe de tous les visiteurs avec un mot de passe haché.
     *
     * Cette fonction récupère tous les visiteurs de la base de données, hache leur mot de passe et met à jour le champ
     * correspondant dans la table `visiteur`.
     *
     * @return void
     */
    public function setMdpVisiteur() {
        $requetePrepare = $this->connexion->prepare(
            'SELECT id, mdp '
            . ' FROM visiteur '
        );
        $requetePrepare->execute();
        $lignes = $requetePrepare->fetchAll(PDO::FETCH_ASSOC);
        foreach ($lignes as $array){
            $id = $array["id"];
            $mdp = $array["mdp"];

            $hashMdp = password_hash($mdp, PASSWORD_DEFAULT);
            $req = $this->connexion->prepare('UPDATE visiteur SET mdp= :hashMdp  WHERE id= :unId ');
            $req -> bindParam(':unId', $id, PDO::PARAM_STR);
            $req -> bindParam(':hashMdp', $hashMdp, PDO::PARAM_STR);
            $req -> execute();
        }
    }

    /**
     * Récupère le mot de passe haché d'un visiteur à partir de son login.
     *
     * Cette fonction retourne le mot de passe haché d'un visiteur donné, en fonction de son login.
     *
     * @param string $login Le login du visiteur pour lequel récupérer le mot de passe.
     * @return string Le mot de passe haché du visiteur.
     */
    public function getMdpVisiteur($login) {
        $requetePrepare = $this->connexion->prepare(
            'SELECT mdp '
            . 'FROM visiteur '
            . 'WHERE visiteur.login = :unLogin'
        );
        $requetePrepare->bindParam(':unLogin', $login, PDO::PARAM_STR);
        $requetePrepare->execute();
        return $requetePrepare->fetch(PDO::FETCH_OBJ)->mdp;
    }

    /**
     * Met à jour les mots de passe de tous les comptables avec un mot de passe haché.
     *
     * Cette fonction récupère tous les comptables de la base de données, hache leur mot de passe et met à jour le champ
     * correspondant dans la table `comptable`.
     *
     * @return void
     */
    public function setMdpComptable() {
        $requetePrepare = $this->connexion->prepare(
            'SELECT id, mdp '
            . ' FROM comptable '
        );
        $requetePrepare->execute();
        $lignes = $requetePrepare->fetchAll(PDO::FETCH_ASSOC);
        foreach ($lignes as $array){
            $id = $array["id"];
            $mdp = $array["mdp"];

            $hashMdp = password_hash($mdp, PASSWORD_DEFAULT);
            $req = $this->connexion->prepare('UPDATE comptable SET mdp= :hashMdp  WHERE id= :unId ');
            $req -> bindParam(':unId', $id, PDO::PARAM_STR);
            $req -> bindParam(':hashMdp', $hashMdp, PDO::PARAM_STR);
            $req -> execute();
        }
    }

    /**
     * Récupère le mot de passe haché d'un comptable à partir de son login.
     *
     * Cette fonction retourne le mot de passe haché d'un comptable donné, en fonction de son login.
     *
     * @param string $login Le login du comptable pour lequel récupérer le mot de passe.
     * @return string Le mot de passe haché du comptable.
     */
    public function getMdpComptable($login) {
        $requetePrepare = $this->connexion->prepare(
            'SELECT mdp '
            . 'FROM comptable '
            . 'WHERE comptable.login = :unLogin'
        );
        $requetePrepare->bindParam(':unLogin', $login, PDO::PARAM_STR);
        $requetePrepare->execute();
        return $requetePrepare->fetch(PDO::FETCH_OBJ)->mdp;
    }

    /**
     * Récupère tous les visiteurs de la base de données.
     *
     * Cette fonction retourne une liste de tous les visiteurs, avec leurs noms, prénoms et logins.
     *
     * @return array Un tableau associatif contenant les informations des visiteurs (nom, prénom, login).
     */
    public function getAllVisiteur()
    {
        $requetePrepare = $this->connexion->prepare('SELECT visiteur.nom, visiteur.prenom, visiteur.login FROM visiteur');
        $requetePrepare->execute();

        $result = $requetePrepare->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }


    /**
     * takes the visiteur login in params and return the id that correspond to the user
     * @param $visiteurLogin
     * @return string|null
     */
    public function getVisiteurId($visiteurLogin) {
        $requetePrepare = $this->connexion->prepare('SELECT visiteur.id FROM visiteur WHERE visiteur.login = :login');
        $requetePrepare->bindParam(':login', $visiteurLogin, PDO::PARAM_STR);
        $requetePrepare->execute();

        $result = $requetePrepare->fetch(PDO::FETCH_ASSOC);

        if ($result && isset($result['id'])) return (string) $result['id'];
        else return null;
    }

    /**Take the id of the visiteur in params and return the month he has in fichefrais
     * @param $idVisiteur
     * @return void
     */
    public function getAllMoisVisiteur($idVisiteur) {
        $requetePrepare = $this->connexion->prepare('SELECT mois FROM fichefrais WHERE fichefrais.idvisiteur = :idvisiteur');
        $requetePrepare->bindParam(':idvisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->execute();

        $result = $requetePrepare->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }


    /**
     * Retourne sous forme d'un tableau associatif toutes les lignes de frais
     * hors forfait concernées par les deux arguments.
     * La boucle foreach ne peut être utilisée ici car on procède
     * à une modification de la structure itérée - transformation du champ date-
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return tous les champs des lignes de frais hors forfait sous la forme
     * d'un tableau associatif
     */
    public function getLesFraisHorsForfait($idVisiteur, $mois): array
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT * FROM lignefraishorsforfait '
            . 'WHERE lignefraishorsforfait.idvisiteur = :unIdVisiteur '
            . 'AND lignefraishorsforfait.mois = :unMois'
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        $lesLignes = $requetePrepare->fetchAll();
        $nbLignes = count($lesLignes);
        for ($i = 0; $i < $nbLignes; $i++) {
            $date = $lesLignes[$i]['date'];
            $lesLignes[$i]['date'] = Utilitaires::dateAnglaisVersFrancais($date);
        }
        return $lesLignes;
    }


    /**
     * Retourne le nombre de justificatif d'un visiteur pour un mois donné
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return le nombre entier de justificatifs
     */
    public function getNbjustificatifs($idVisiteur, $mois): int
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT fichefrais.nbjustificatifs as nb FROM fichefrais '
            . 'WHERE fichefrais.idvisiteur = :unIdVisiteur '
            . 'AND fichefrais.mois = :unMois'
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        $laLigne = $requetePrepare->fetch();
        return $laLigne['nb'];
    }

    /**
     * Retourne sous forme d'un tableau associatif toutes les lignes de frais
     * au forfait concernées par les deux arguments
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return l'id, le libelle et la quantité sous la forme d'un tableau
     * associatif
     */
    public function getLesFraisForfait($idVisiteur, $mois): array
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT fraisforfait.id as idfrais, '
            . 'fraisforfait.libelle as libelle, '
            . 'lignefraisforfait.quantite as quantite '
            . 'FROM lignefraisforfait '
            . 'INNER JOIN fraisforfait '
            . 'ON fraisforfait.id = lignefraisforfait.idfraisforfait '
            . 'WHERE lignefraisforfait.idvisiteur = :unIdVisiteur '
            . 'AND lignefraisforfait.mois = :unMois '
            . 'ORDER BY lignefraisforfait.idfraisforfait'
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        return $requetePrepare->fetchAll();
    }

    /** Fonction qui prend l'id visiteur, les mois et le frais en paramètre, et les insère en bd
     *
     * @param $idVisiteur
     * @param $mois
     * @param $unFrais
     * @return void
     */
    public function setLesFraisForfait($idVisiteur, $mois, $unFrais): void {
        $requeteSQL = 'UPDATE lignefraisforfait SET quantite = CASE idfraisforfait ';
        foreach ($unFrais as $idFraisForfait => $quantite) {
            $requeteSQL .= 'WHEN :idFraisForfait' . $idFraisForfait . ' THEN :quantite' . $idFraisForfait . ' ';
        }
        $requeteSQL .= 'END WHERE idvisiteur = :unIdVisiteur AND mois = :unMois';

        $requetePrepare = $this->connexion->prepare($requeteSQL);
        foreach ($unFrais as $idFraisForfait => $quantite) {
            $requetePrepare->bindValue(':idFraisForfait' . $idFraisForfait, $idFraisForfait, PDO::PARAM_STR);
            $requetePrepare->bindValue(':quantite' . $idFraisForfait, $quantite, PDO::PARAM_INT);
        }
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);

        $requetePrepare->execute();
    }

    /**
     * Réinitialise les frais forfaitaires pour un visiteur et un mois donnés.
     *
     * Cette fonction met à zéro la quantité des frais forfaitaires pour un visiteur donné et un mois spécifique,
     * dans la table `lignefraisforfait`.
     *
     * @param string $idVisiteur L'identifiant du visiteur pour lequel réinitialiser les frais forfaitaires.
     * @param string $mois Le mois pour lequel réinitialiser les frais forfaitaires.
     * @return void
     */
    public function resetLesFraisForfait($idVisiteur, $mois): void {
        $requeteSQL = 'UPDATE lignefraisforfait SET quantite = 0 WHERE idvisiteur = :idVisiteur AND mois = :mois';
        $requetePrepare = $this->connexion->prepare($requeteSQL);
        $requetePrepare->bindParam(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':mois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
    }

    /**
     * Met à jour les frais hors forfait pour un visiteur et un mois donnés.
     *
     * Cette fonction met à jour les frais hors forfait pour un visiteur et un mois donnés.
     * Pour chaque frais hors forfait, elle modifie la date, le libellé et le montant dans la base de données.
     *
     * @param string $idVisiteur L'identifiant du visiteur pour lequel mettre à jour les frais hors forfait.
     * @param string $mois Le mois pour lequel mettre à jour les frais hors forfait.
     * @param array $lesFraisHorsForfait Un tableau associatif contenant les frais hors forfait à mettre à jour.
     *                                    Chaque élément contient la date, le libellé et le montant du frais.
     * @return void
     */
    public function setLesFraisHorsForfait($idVisiteur, $mois, $lesFraisHorsForfait): void {
        foreach ($lesFraisHorsForfait as $idFraisHorsForfait => $frais) {
            $requeteSQL = 'UPDATE lignefraishorsforfait 
                       SET date = :date, libelle = :libelle, montant = :montant 
                       WHERE id = :idFraisHorsForfait AND idvisiteur = :idVisiteur AND mois = :mois';

            $requetePrepare = $this->connexion->prepare($requeteSQL);
            $requetePrepare->bindParam(':date', $frais['date'], PDO::PARAM_STR);
            $requetePrepare->bindParam(':libelle', $frais['libelle'], PDO::PARAM_STR);
            $requetePrepare->bindParam(':montant', $frais['montant'], PDO::PARAM_INT);
            $requetePrepare->bindParam(':idFraisHorsForfait', $idFraisHorsForfait, PDO::PARAM_STR);
            $requetePrepare->bindParam(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
            $requetePrepare->bindParam(':mois', $mois, PDO::PARAM_STR);
            $requetePrepare->execute();
        }
    }

    /**
     * Réinitialise les frais hors forfait pour un visiteur et un mois donnés.
     *
     * Cette fonction supprime tous les frais hors forfait pour un visiteur donné et un mois spécifique
     * de la table `lignefraishorsforfait`.
     *
     * @param string $idVisiteur L'identifiant du visiteur pour lequel réinitialiser les frais hors forfait.
     * @param string $mois Le mois pour lequel réinitialiser les frais hors forfait.
     * @return void
     */
    public function resetUnFraisHorsForfait($idVisiteur, $mois, $idFrais): void {
        $requeteSQL = 'UPDATE lignefraishorsforfait 
                   SET libelle = ancien_libelle, 
                       date = ancien_date, 
                       montant = ancien_montant
                   WHERE id = :idFrais 
                     AND idvisiteur = :idVisiteur 
                     AND mois = :mois';

        $requetePrepare = $this->connexion->prepare($requeteSQL);
        $requetePrepare->bindParam(':idFrais', $idFrais, PDO::PARAM_INT);
        $requetePrepare->bindParam(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':mois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
    }



    public function updateFraisHorsForfait($idFrais, $idVisiteur, $mois, $date, $libelle, $montant): void {
        $requeteSQL = 'UPDATE lignefraishorsforfait 
                   SET ancien_libelle = libelle, 
                       ancien_date = date, 
                       ancien_montant = montant, 
                       libelle = :libelle, 
                       date = :date, 
                       montant = :montant 
                   WHERE id = :idFrais 
                     AND idvisiteur = :idVisiteur 
                     AND mois = :mois';

        $requetePrepare = $this->connexion->prepare($requeteSQL);
        $requetePrepare->bindParam(':idFrais', $idFrais, PDO::PARAM_INT);
        $requetePrepare->bindParam(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':mois', $mois, PDO::PARAM_STR);
        $requetePrepare->bindParam(':date', $date, PDO::PARAM_STR);
        $requetePrepare->bindParam(':libelle', $libelle, PDO::PARAM_STR);
        $requetePrepare->bindParam(':montant', $montant, PDO::PARAM_STR);
        $requetePrepare->execute();
    }

    /**
     * @param $idVisiteur
     * @param $mois
     * @param $idFrais
     * @return void
     */
    public function refuserFraisHorsForfait($idVisiteur, $mois, $idFrais): void {
        $sql = "UPDATE lignefraishorsforfait 
            SET ancien_libelle = libelle,
                libelle = CONCAT('REFUSE : ', libelle)
            WHERE id = :idFrais
              AND idvisiteur = :idVisiteur
              AND mois = :mois
              AND libelle NOT LIKE 'REFUSE : %'";

        $requete = $this->connexion->prepare($sql);
        $requete->bindParam(':idFrais', $idFrais, PDO::PARAM_INT);
        $requete->bindParam(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requete->bindParam(':mois', $mois, PDO::PARAM_STR);
        $requete->execute();
    }




    /**
     * Retourne tous les id de la table FraisForfait
     *
     * @return un tableau associatif
     */
    public function getLesIdFrais(): array
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT fraisforfait.id as idfrais '
            . 'FROM fraisforfait ORDER BY fraisforfait.id'
        );
        $requetePrepare->execute();
        return $requetePrepare->fetchAll();
    }

    /**
     * Met à jour la table ligneFraisForfait
     * Met à jour la table ligneFraisForfait pour un visiteur et
     * un mois donné en enregistrant les nouveaux montants
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     * @param Array  $lesFrais   tableau associatif de clé idFrais et
     *                           de valeur la quantité pour ce frais
     *
     * @return null
     */
    public function majFraisForfait($idVisiteur, $mois, $lesFrais): void
    {
        $lesCles = array_keys($lesFrais);
        foreach ($lesCles as $unIdFrais) {
            $qte = $lesFrais[$unIdFrais];
            $requetePrepare = $this->connexion->prepare(
                'UPDATE lignefraisforfait '
                . 'SET lignefraisforfait.quantite = :uneQte '
                . 'WHERE lignefraisforfait.idvisiteur = :unIdVisiteur '
                . 'AND lignefraisforfait.mois = :unMois '
                . 'AND lignefraisforfait.idfraisforfait = :idFrais'
            );
            $requetePrepare->bindParam(':uneQte', $qte, PDO::PARAM_INT);
            $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
            $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
            $requetePrepare->bindParam(':idFrais', $unIdFrais, PDO::PARAM_STR);
            $requetePrepare->execute();
        }
    }

    /**
     * Met à jour le nombre de justificatifs de la table ficheFrais
     * pour le mois et le visiteur concerné
     *
     * @param String  $idVisiteur      ID du visiteur
     * @param String  $mois            Mois sous la forme aaaamm
     * @param Integer $nbJustificatifs Nombre de justificatifs
     *
     * @return null
     */
    public function majNbJustificatifs($idVisiteur, $mois, $nbJustificatifs): void
    {
        $requetePrepare = $this->connexion->prepare(
            'UPDATE fichefrais '
            . 'SET nbjustificatifs = :unNbJustificatifs '
            . 'WHERE fichefrais.idvisiteur = :unIdVisiteur '
            . 'AND fichefrais.mois = :unMois'
        );
        $requetePrepare->bindParam(
            ':unNbJustificatifs',
            $nbJustificatifs,
            PDO::PARAM_INT
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
    }

    /**
     * Teste si un visiteur possède une fiche de frais pour le mois passé en argument
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return vrai ou faux
     */
    public function estPremierFraisMois($idVisiteur, $mois): bool
    {
        $boolReturn = false;
        $requetePrepare = $this->connexion->prepare(
            'SELECT fichefrais.mois FROM fichefrais '
            . 'WHERE fichefrais.mois = :unMois '
            . 'AND fichefrais.idvisiteur = :unIdVisiteur'
        );
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->execute();
        if (!$requetePrepare->fetch()) {
            $boolReturn = true;
        }
        return $boolReturn;
    }

    /**
     * Retourne le dernier mois en cours d'un visiteur
     *
     * @param String $idVisiteur ID du visiteur
     *
     * @return le mois sous la forme aaaamm
     */
    public function dernierMoisSaisi($idVisiteur): string
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT MAX(mois) as dernierMois '
            . 'FROM fichefrais '
            . 'WHERE fichefrais.idvisiteur = :unIdVisiteur'
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->execute();
        $laLigne = $requetePrepare->fetch();
        $dernierMois = $laLigne['dernierMois'];
        return $dernierMois;
    }

    /**
     * Crée une nouvelle fiche de frais et les lignes de frais au forfait
     * pour un visiteur et un mois donnés
     *
     * Récupère le dernier mois en cours de traitement, met à 'CL' son champs
     * idEtat, crée une nouvelle fiche de frais avec un idEtat à 'CR' et crée
     * les lignes de frais forfait de quantités nulles
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return null
     */
    public function creeNouvellesLignesFrais($idVisiteur, $mois): void
    {
        $dernierMois = $this->dernierMoisSaisi($idVisiteur);
        $laDerniereFiche = $this->getLesInfosFicheFrais($idVisiteur, $dernierMois);
        if ($laDerniereFiche['idEtat'] == 'CR') {
            $this->majEtatFicheFrais($idVisiteur, $dernierMois, 'CL');
        }
        $requetePrepare = $this->connexion->prepare(
            'INSERT INTO fichefrais (idvisiteur,mois,nbjustificatifs,'
            . 'montantvalide,datemodif,idetat) '
            . "VALUES (:unIdVisiteur,:unMois,0,0,now(),'CR')"
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        $lesIdFrais = $this->getLesIdFrais();
        foreach ($lesIdFrais as $unIdFrais) {
            $requetePrepare = $this->connexion->prepare(
                'INSERT INTO lignefraisforfait (idvisiteur,mois,'
                . 'idfraisforfait,quantite) '
                . 'VALUES(:unIdVisiteur, :unMois, :idFrais, 0)'
            );
            $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
            $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
            $requetePrepare->bindParam(':idFrais', $unIdFrais['idfrais'], PDO::PARAM_STR);
            $requetePrepare->execute();
        }
    }

    /**
     * Crée un nouveau frais hors forfait pour un visiteur un mois donné
     * à partir des informations fournies en paramètre
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     * @param String $libelle    Libellé du frais
     * @param String $date       Date du frais au format français jj//mm/aaaa
     * @param Float  $montant    Montant du frais
     *
     * @return null
     */
    public function creeNouveauFraisHorsForfait($idVisiteur, $mois, $libelle, $date, $montant): void
    {
        $dateFr = Utilitaires::dateFrancaisVersAnglais($date);
        $requetePrepare = $this->connexion->prepare(
            'INSERT INTO lignefraishorsforfait '
            . 'VALUES (null, :unIdVisiteur,:unMois, :unLibelle, :uneDateFr,'
            . ':unMontant) '
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unLibelle', $libelle, PDO::PARAM_STR);
        $requetePrepare->bindParam(':uneDateFr', $dateFr, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMontant', $montant, PDO::PARAM_INT);
        $requetePrepare->execute();
    }

    /**
     * Supprime le frais hors forfait dont l'id est passé en argument
     *
     * @param String $idFrais ID du frais
     *
     * @return null
     */
    public function supprimerFraisHorsForfait($idFrais): void
    {
        $requetePrepare = $this->connexion->prepare(
            'DELETE FROM lignefraishorsforfait '
            . 'WHERE lignefraishorsforfait.id = :unIdFrais'
        );
        $requetePrepare->bindParam(':unIdFrais', $idFrais, PDO::PARAM_STR);
        $requetePrepare->execute();
    }

    /**
     * Retourne les mois pour lesquel un visiteur a une fiche de frais
     *
     * @param String $idVisiteur ID du visiteur
     *
     * @return un tableau associatif de clé un mois -aaaamm- et de valeurs
     *         l'année et le mois correspondant
     */
    public function getLesMoisDisponibles($idVisiteur): array
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT fichefrais.mois AS mois FROM fichefrais '
            . 'WHERE fichefrais.idvisiteur = :unIdVisiteur '
            . 'ORDER BY fichefrais.mois desc'
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->execute();
        $lesMois = array();
        while ($laLigne = $requetePrepare->fetch()) {
            $mois = $laLigne['mois'];
            $numAnnee = substr($mois, 0, 4);
            $numMois = substr($mois, 4, 2);
            $lesMois[] = array(
                'mois' => $mois,
                'numAnnee' => $numAnnee,
                'numMois' => $numMois
            );
        }
        return $lesMois;
    }

    /**
     * Retourne les informations d'une fiche de frais d'un visiteur pour un
     * mois donné
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return un tableau avec des champs de jointure entre une fiche de frais
     *         et la ligne d'état
     */
    public function getLesInfosFicheFrais($idVisiteur, $mois): array
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT fichefrais.idetat as idEtat, '
            . 'fichefrais.datemodif as dateModif,'
            . 'fichefrais.nbjustificatifs as nbJustificatifs, '
            . 'fichefrais.montantvalide as montantValide, '
            . 'etat.libelle as libEtat '
            . 'FROM fichefrais '
            . 'INNER JOIN etat ON fichefrais.idetat = etat.id '
            . 'WHERE fichefrais.idvisiteur = :unIdVisiteur '
            . 'AND fichefrais.mois = :unMois'
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        $laLigne = $requetePrepare->fetch();
        return $laLigne;
    }

    /**
     * Modifie l'état et la date de modification d'une fiche de frais.
     * Modifie le champ idEtat et met la date de modif à aujourd'hui.
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     * @param String $etat       Nouvel état de la fiche de frais
     *
     * @return null
     */
    public function majEtatFicheFrais($idVisiteur, $mois, $etat): void
    {
        $requetePrepare = $this->connexion->prepare(
            'UPDATE fichefrais '
            . 'SET idetat = :unEtat, datemodif = now() '
            . 'WHERE fichefrais.idvisiteur = :unIdVisiteur '
            . 'AND fichefrais.mois = :unMois'
        );
        $requetePrepare->bindParam(':unEtat', $etat, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
    }

    /**
     * Récupère les mois de frais clôturés pour un visiteur donné.
     *
     * Cette fonction retourne tous les mois durant lesquels un visiteur donné a des frais clôturés
     * (état 'CL' dans la base de données).
     *
     * @param string $idVisiteur L'identifiant du visiteur pour lequel récupérer les mois de frais clôturés.
     * @return array Un tableau associatif contenant les mois de frais clôturés pour le visiteur.
     */
    public function getMoisCloturesVisiteur($idVisiteur)
    {
        $requetePrepare = $this->connexion->prepare (
            'SELECT mois '
            . 'FROM fichefrais'
            . " WHERE idvisiteur = :unIdvisiteur AND idetat = 'CL' "
        );
        $requetePrepare->bindParam(':unIdvisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->execute();
        $result = $requetePrepare->fetchAll(PDO::FETCH_ASSOC);
        return $result;

    }

    /**
     * Récupère le montant unitaire d'un frais forfaitaire.
     *
     * Cette fonction retourne le montant d'un frais forfaitaire spécifique, en utilisant son ID dans la base de données.
     * Si aucun montant n'est trouvé, la fonction retourne 0.0.
     *
     * @param string $idFrais L'identifiant du frais forfaitaire pour lequel récupérer le montant.
     * @return float Le montant unitaire du frais, ou 0.0 si le frais n'existe pas.
     */
    public function getMontantUnitaire($idFrais): float
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT montant FROM fraisforfait WHERE id = :idFrais'
        );
        $requetePrepare->bindParam(':idFrais', $idFrais, PDO::PARAM_STR);
        $requetePrepare->execute();
        $result = $requetePrepare->fetch();
        return $result['montant'] ?? 0.0;
    }

    /**
     * Génère un PDF de remboursement de frais pour un visiteur et un mois donnés.
     * Si le PDF a déjà été généré pour ce visiteur et ce mois, il est récupéré depuis la base de données
     * et envoyé directement au client pour téléchargement. Sinon, le PDF est généré et stocké dans la base de données,
     * puis envoyé au client.
     *
     * @param string $idVisiteur L'identifiant du visiteur pour lequel le PDF est généré.
     * @param string $mois Le mois pour lequel le PDF est généré, au format YYYYMM.
     * @return void
     */
    public function generatePdf($idVisiteur, $mois) // Note: make this function thinner if possible if not makes other function that you call inside this one
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT pdf_content FROM pdf_reports WHERE id_visiteur = :idVisiteur AND mois = :mois'
        );
        $requetePrepare->bindParam(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':mois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        $result = $requetePrepare->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $pdfContent = $result['pdf_content'];
            header('Content-Description: File Transfer');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="RapportFrais.pdf"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . strlen($pdfContent));
            echo $pdfContent;
            exit;
        }

        ob_start();
        $lesFraisForfait = $this->getLesFraisForfait($idVisiteur, $mois);
        $lesFraisHorsForfait = $this->getLesFraisHorsForfait($idVisiteur, $mois);

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);

        $pdf->Cell(0, 10, mb_convert_encoding('REMBOURSEMENT DE FRAIS ENGAGES', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $pdf->Ln(10);

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(40, 10, 'Visiteur : ' . $idVisiteur);
        $pdf->Ln(6);
        $pdf->Cell(40, 10, 'Mois : ' . Utilitaires::moisEnFrancais($mois));
        $pdf->Ln(10);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(70, 7, 'Frais Forfaitaires', 1, 0, 'C');
        $pdf->Cell(30, 7, 'Quantité', 1, 0, 'C');
        $pdf->Cell(40, 7, 'Montant unitaire', 1, 0, 'C');
        $pdf->Cell(40, 7, 'Total', 1, 1, 'C');

        $pdf->SetFont('Arial', '', 12);
        $totalForfait = 0;
        foreach ($lesFraisForfait as $frais) {
            $quantite = $frais['quantite'];
            $montantUnitaire = $this->getMontantUnitaire($frais['idfrais']);
            $total = $quantite * $montantUnitaire;
            $totalForfait += $total;

            $pdf->Cell(70, 7,  mb_convert_encoding($frais['libelle'], 'ISO-8859-1', 'UTF-8'), 1);
            $pdf->Cell(30, 7, $quantite, 1, 0, 'R');
            $pdf->Cell(40, 7, number_format($montantUnitaire, 2, ',', ' '), 1, 0, 'R');
            $pdf->Cell(40, 7, number_format($total, 2, ',', ' '), 1, 1, 'R');
        }

        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 7, 'Date', 1, 0, 'C');
        $pdf->Cell(100, 7, mb_convert_encoding('Libellé', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
        $pdf->Cell(40, 7, 'Montant', 1, 1, 'C');

        $pdf->SetFont('Arial', '', 12);
        $totalHorsForfait = 0;
        foreach ($lesFraisHorsForfait as $frais) {
            $totalHorsForfait += $frais['montant'];

            $pdf->Cell(40, 7, $frais['date'], 1);
            $pdf->Cell(100, 7, mb_convert_encoding($frais['libelle'], 'ISO-8859-1', 'UTF-8'), 1);
            $pdf->Cell(40, 7, number_format($frais['montant'], 2, ',', ' '), 1, 1, 'R');
        }

        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(150, 7, 'TOTAL ' . substr($mois, 4, 2) . '/' . substr($mois, 0, 4), 1);
        $pdf->Cell(40, 7, number_format($totalForfait + $totalHorsForfait, 2, ',', ' '), 1, 1, 'R');

        $pdfContent = $pdf->Output('S');

        $requetePrepare = $this->connexion->prepare(
            'INSERT INTO pdf_reports (id_visiteur, mois, pdf_content) VALUES (:idVisiteur, :mois, :pdfContent)'
        );
        $requetePrepare->bindParam(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':mois', $mois, PDO::PARAM_STR);
        $requetePrepare->bindParam(':pdfContent', $pdfContent, PDO::PARAM_LOB);
        $requetePrepare->execute();

        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="RapportFrais.pdf"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($pdfContent));
        echo $pdfContent;
        exit;
    }


}
