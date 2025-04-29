<?php
error_reporting(0);
ini_set('display_errors', 0);
?>
<div class="form-section">
    <h3>Frais Forfaitisés</h3>
    <form method="POST" action="index.php?uc=gererFraisComptable&action=corrigerFrais">
        <?php foreach ($lesFraisForfait as $frais) : ?>
            <label for="<?php echo $frais['idfrais']; ?>"><?php echo htmlspecialchars($frais['libelle']); ?></label>
            <input type="text" name="lesFrais[<?php echo $frais['idfrais']; ?>]" value="<?php echo $frais['quantite']; ?>">
        <?php endforeach; ?>
        <input type="hidden" name="visiteur" value="<?php echo htmlspecialchars($visiteurLogin); ?>">
        <input type="hidden" name="mois" value="<?php echo htmlspecialchars($mois); ?>">
        <button type="submit">Corriger</button>
    </form>

    <h3>Frais Hors Forfait</h3>
    <table>
        <thead>
        <tr>
            <th>Date</th>
            <th>Libellé</th>
            <th>Montant</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($lesFraisHorsForfait as $fraisHors) : ?>
            <tr>
                <form method="POST" action="index.php?uc=gererFraisComptable&action=corrigerFraisHorsForfait">
                    <td class="date">
                        <?php
                        $dateFraisFormattee = '1970-01-01';
                        if (!empty($fraisHors['date'])) {
                            $dateObj = DateTime::createFromFormat('Y-m-d', $fraisHors['date']);
                            if ($dateObj) {
                                $dateFraisFormattee = $dateObj->format('Y-m-d');
                            } else {
                                $dateObj = DateTime::createFromFormat('d/m/Y', $fraisHors['date']);
                                if ($dateObj) $dateFraisFormattee = $dateObj->format('Y-m-d');
                            }
                        }
                        ?>
                        <input type="date" name="dateFrais" value="<?php echo htmlspecialchars($dateFraisFormattee); ?>" class="form-control">
                    </td>
                    <td class="libelle">
                        <input type="text" name="libelleFrais" value="<?php echo htmlspecialchars($fraisHors['libelle']); ?>" class="form-control">
                    </td>
                    <td class="montant">
                        <input type="text" name="montantFrais" value="<?php echo htmlspecialchars($fraisHors['montant']); ?>" class="form-control">
                    </td>
                    <td class="actions">
                        <input type="hidden" name="idFrais" value="<?php echo $fraisHors['id']; ?>">
                        <input type="hidden" name="visiteur" value="<?php echo htmlspecialchars($visiteurLogin); ?>">
                        <input type="hidden" name="mois" value="<?php echo htmlspecialchars($mois); ?>">

                        <button class="btn btn-warning btn-sm" type="submit">Corriger</button>
                    </td>
                </form>

                <form method="POST" action="index.php?uc=gererFraisComptable&action=reinitialiserFrais">
                    <td class="actions">
                        <input type="hidden" name="idFrais" value="<?php echo $fraisHors['id']; ?>">
                        <input type="hidden" name="visiteur" value="<?php echo htmlspecialchars($visiteurLogin); ?>">
                        <input type="hidden" name="mois" value="<?php echo htmlspecialchars($mois); ?>">

                        <button class="btn btn-danger btn-sm" type="submit">Réinitialiser</button>
                    </td>
                </form>

                <form method="POST" action="index.php?uc=gererFraisComptable&action=refuserFrais">
                    <td class="actions">
                        <input type="hidden" name="idFrais" value="<?php echo $fraisHors['id']; ?>">
                        <input type="hidden" name="visiteur" value="<?php echo htmlspecialchars($visiteurLogin); ?>">
                        <input type="hidden" name="mois" value="<?php echo htmlspecialchars($mois); ?>">

                        <button class="btn btn-danger btn-sm" type="submit">Refuser</button>
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <input type="hidden" name="visiteur" value="<?php echo htmlspecialchars($visiteurLogin); ?>">
        <input type="hidden" name="mois" value="<?php echo htmlspecialchars($mois); ?>">
    </form>

    <form method="POST" action="index.php?uc=gererFraisComptable&action=genererPDF">
        <input type="hidden" name="visiteur" value="<?php echo htmlspecialchars($visiteurLogin); ?>">
        <input type="hidden" name="mois" value="<?php echo htmlspecialchars($mois); ?>">
        <button type="submit" class="btn btn-primary">Générer le PDF</button>
    </form>
</div>

