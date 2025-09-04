import { Component, OnInit } from '@angular/core';
import { PanierService } from '../../services/panier.service';
import { ProduitPanier } from '../../models/produit-panier.model';
import { CommonModule, CurrencyPipe } from '@angular/common';

@Component({
  selector: 'app-panier',
  standalone: true,          
  imports: [CommonModule],
  templateUrl: './panier.component.html',
  styleUrls: ['./panier.component.scss']
})
export class CommandesPanierComponent implements OnInit {
  produits: ProduitPanier[] = [];

  constructor(private panierService: PanierService) {}

  ngOnInit(): void {
    this.produits = this.panierService.getPanier();
  }

  getTotal(): number {
    return this.produits.reduce((sum, p) => sum + p.prix * p.quantite, 0);
  }
}
