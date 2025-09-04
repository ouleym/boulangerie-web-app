import { Component, OnInit } from '@angular/core';
import { Promotion } from '../../models/promotion.model';
import { PromotionService } from '../../services/promotion.service';
import { CommonModule } from '@angular/common';


@Component({
  selector: 'app-promotion-list',
  standalone: true, 
  imports: [CommonModule], 
  templateUrl: './list.component.html',
  styleUrls: ['./list.component.scss']
})
export class PromotionsListComponent implements OnInit {
  promotions: Promotion[] = [];

  constructor(private promotionService: PromotionService) { }

  ngOnInit(): void {
    this.loadPromotions();
  }

  loadPromotions(): void {
    this.promotionService.getPromotions().subscribe({
    next: (res) => this.promotions = res.data, 
    error: (err) => console.error(err)
  });
  }
}
