import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Produit } from '../../models/produit.model';

@Component({
  selector: 'app-card',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './card.component.html',
  styleUrls: ['./card.component.scss']
})
export class CardComponent {
  @Input() produit!: Produit;
}
