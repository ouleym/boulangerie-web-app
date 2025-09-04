import { Component, Input } from '@angular/core';
import { Promotion } from '../../models/promotion.model';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-promotion-detail',
  standalone: true, 
  imports: [CommonModule], 
  templateUrl: './detail.component.html',
  styleUrls: ['./detail.component.scss']
})
export class PromotionsDetailComponent {
  @Input() promotion!: Promotion;
}
