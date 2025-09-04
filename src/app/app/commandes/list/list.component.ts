import { Component, OnInit } from '@angular/core';
import { Commande } from '../../models/commande.model';
import { CommandeService } from '../../services/commande.service';
import { CommonModule, CurrencyPipe } from '@angular/common';

@Component({
  selector: 'app-commande-list',
  standalone: true,          
  imports: [CommonModule],
  templateUrl: './list.component.html',
  styleUrls: ['./list.component.scss']
})
export class CommandesListComponent implements OnInit {
  commandes: Commande[] = [];

  constructor(private commandeService: CommandeService) {}

  ngOnInit(): void {
    this.loadCommandes();
  }

  loadCommandes(): void {
    this.commandeService.getCommandes().subscribe({
      next: (res) => this.commandes = res.data,
      error: (err) => console.error(err)
    });
  }

}
