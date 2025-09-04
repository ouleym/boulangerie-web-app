
import { Component, EventEmitter, Input, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Produit } from '../../models/produit.model';

@Component({
  selector: 'app-form',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './form.component.html',
  styleUrls: ['./form.component.scss']
})
export class ProduitsFormComponent {
  @Input() produit: Produit = {
    id: 0,
    nom: '',
    description: '',
    prix: 0,
    stock: 0,
    categorieId: 0,
    imageUrl: ''
  };

  @Output() save = new EventEmitter<Produit>();

  onSubmit() {
    this.save.emit(this.produit);
  }

}
