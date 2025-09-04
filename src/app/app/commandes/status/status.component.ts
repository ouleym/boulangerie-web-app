import { Component, Input } from '@angular/core';
import { Commande } from '../../models/commande.model';

@Component({
  selector: 'app-commande-status',
  templateUrl: './status.component.html',
  styleUrls: ['./status.component.scss']
})
export class CommandesStatusComponent {
  @Input() commande!: Commande;
}
