<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
<<<<<<< HEAD
use Illuminate\Http\Request;
=======
use App\Models\Facture;
use App\Models\Commande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PDF;
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)

class FactureController extends Controller
{
    /**
<<<<<<< HEAD
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
=======
     * Liste des factures avec leur commande.
     */
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
>>>>>>> 625c931 (Ajout de la partie backend Laravel complète)
    }
}
