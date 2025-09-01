<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FactureController extends Controller
{
    public function index()
    {
        return response()->json(Facture::with('commande')->get());
    }

    /**
     * Générer une facture PDF pour une commande.
     */
    public function store(Request $request)
    {
        $request->validate([
            'commande_id' => 'required|exists:commandes,id'
        ]);

        $commande = Commande::with('produits')->findOrFail($request->commande_id);

        $montant = $commande->total;

        // Générer PDF (exemple avec dompdf)
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('facture', compact('commande'));
        $fileName = 'facture_' . $commande->id . '.pdf';
        Storage::put('public/factures/' . $fileName, $pdf->output());

        $facture = Facture::create([
            'commande_id' => $commande->id,
            'montant' => $montant,
            'pdf_path' => 'storage/factures/' . $fileName
        ]);

        return response()->json($facture, 201);
    }

    /**
     * Afficher une facture.
     */
    public function show(string $id)
    {
        $facture = Facture::with('commande')->findOrFail($id);
        return response()->json($facture);
    }

    /**
     * Supprimer une facture.
     */
    public function destroy(string $id)
    {
        Facture::destroy($id);
        return response()->json(null, 204);
    }
}
