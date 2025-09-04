import { Component, Input } from '@angular/core';
import { Categorie } from '../../models/categorie.model';
import { CommonModule, CurrencyPipe } from '@angular/common';


@Component({
  selector: 'app-categorie-detail',
  standalone: true,          
  imports: [CommonModule],
  templateUrl: './detail.component.html',
  styleUrls: ['./detail.component.scss']
})
export class CategoriesDetailComponent {
  @Input() categorie!: Categorie;
}
