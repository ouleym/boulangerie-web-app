<<<<<<< HEAD
import { Component } from '@angular/core';

@Component({
  selector: 'app-list-categorie',
  imports: [],
  templateUrl: './list.component.html',
  styleUrl: './list.component.scss'
})
export class CategoriesListComponent {
=======
import { Component, OnInit } from '@angular/core';
import { Categorie } from '../../models/categorie.model';
import { CategorieService } from '../../services/categorie.service';
import { CommonModule, CurrencyPipe } from '@angular/common';

@Component({
  selector: 'app-categorie-list',
  standalone: true,          
  imports: [CommonModule],
  templateUrl: './list.component.html',
  styleUrls: ['./list.component.scss']
})
export class CategoriesListComponent implements OnInit {
  categories: Categorie[] = [];

  constructor(private categorieService: CategorieService) { }

  ngOnInit(): void {
    this.loadCategories();
  }

  loadCategories(): void {
  this.categorieService.getAllCategories().subscribe({
    next: (res) => this.categories = res.data,
    error: (err) => console.error(err)
  });
}
>>>>>>> 4ae0300 (Initial commit de mon projet frontend)

}
