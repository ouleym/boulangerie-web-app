
import { Component, Input } from '@angular/core';
import { NgIf, CurrencyPipe } from '@angular/common';
import { Produit } from '../../models/produit.model';

@Component({
  selector: 'app-produits-detail',
  standalone: true,
  imports: [NgIf, CurrencyPipe],
  templateUrl: './detail.component.html',
  styleUrls: ['./detail.component.scss']
})
export class ProduitsDetailComponent {
  @Input() produit!: Produit; 
}
