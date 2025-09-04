import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Produit } from '../../models/produit.model';
import { CardComponent } from '../card/card.component';

@Component({
  selector: 'app-list',
  standalone: true,
  imports: [CommonModule, CardComponent],
  templateUrl: './list.component.html',
  styleUrls: ['./list.component.scss']
})
export class ProduitsListComponent {
  @Input() produits: Produit[] = [];
}
