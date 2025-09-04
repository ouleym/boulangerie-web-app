import { Component, Input } from '@angular/core';
import { Commande } from '../../models/commande.model';
import { CommonModule, CurrencyPipe } from '@angular/common';


@Component({
  selector: 'app-commande-detail',
  standalone: true,          
  imports: [CommonModule],
  templateUrl: './detail.component.html',
  styleUrls: ['./detail.component.scss']
})
export class CommandesDetailComponent {
  @Input() commande!: Commande;
}
