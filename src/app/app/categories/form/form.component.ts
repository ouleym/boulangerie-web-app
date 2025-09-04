import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { CategorieService } from '../../services/categorie.service';
import { Categorie } from '../../models/categorie.model';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-categorie-form',
  standalone: true, 
  imports: [ReactiveFormsModule, CommonModule],
  templateUrl: './form.component.html',
  styleUrls: ['./form.component.scss']
})
export class CategoriesFormComponent implements OnInit {
  categorieForm!: FormGroup;

  constructor(
    private fb: FormBuilder,
    private categorieService: CategorieService
  ) {}

  ngOnInit(): void {
    this.categorieForm = this.fb.group({
      nom: ['', Validators.required],
      description: ['']
    });
  }

  submitForm(): void {
    if (this.categorieForm.valid) {
      const newCategorie: Categorie = this.categorieForm.value;
      this.categorieService.createCategorie(newCategorie).subscribe({
        next: (res) => {
          console.log('✅ Catégorie créée', res);
          this.categorieForm.reset();
        },
        error: (err) => console.error('❌ Erreur API', err)
      });
    }
  }
}
