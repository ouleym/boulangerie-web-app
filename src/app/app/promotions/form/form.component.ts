import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { PromotionService } from '../../services/promotion.service';
import { Promotion } from '../../models/promotion.model';

@Component({
  selector: 'app-promotion-form',
  standalone: true,
  imports: [ReactiveFormsModule], 
  templateUrl: './form.component.html',
  styleUrls: ['./form.component.scss']
})
export class PromotionsFormComponent implements OnInit {
  promotionForm!: FormGroup;

  constructor(private fb: FormBuilder, private promotionService: PromotionService) { }

  ngOnInit(): void {
    this.promotionForm = this.fb.group({
      titre: ['', Validators.required], // mettre le même nom que le model
      description: [''],
      reductionPourcentage: [0, [Validators.required, Validators.min(0), Validators.max(100)]],
      dateDebut: ['', Validators.required],
      dateFin: ['', Validators.required]
    });
  }

  submitForm(): void {
    if (this.promotionForm.valid) {
      const newPromotion: Promotion = this.promotionForm.value;
      this.promotionService.createPromotion(newPromotion).subscribe({
        next: (res) => {
          console.log('Promotion créée', res);
          this.promotionForm.reset();
        },
        error: (err) => console.error(err)
      });
    }
  }

}
