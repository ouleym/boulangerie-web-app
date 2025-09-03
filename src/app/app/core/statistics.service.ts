import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { environment } from '../../../environments/environment.development';

export interface DailySales {
  date: string;
  sales: number;
  amount: number;
}

export interface MonthlySales {
  month: string;
  sales: number;
  amount: number;
}

export interface ProductSales {
  productName: string;
  quantity: number;
  revenue: number;
  percentage: number;
}

export interface CategorySales {
  categoryName: string;
  sales: number;
  amount: number;
}

export interface StatsSummary {
  totalSales: number;
  totalRevenue: number;
  totalProducts: number;
  totalCustomers: number;
  averageOrderValue: number;
  growthPercentage: number;
}

export interface HourlyStats {
  hour: string;
  sales: number;
}

@Injectable({
  providedIn: 'root'
})
export class StatisticsService {
  private apiUrl = `${environment.ApiUrl}/statistics`;

  constructor(private http: HttpClient) { }

  // ===== STATISTIQUES PRINCIPALES =====
  
  getStatsSummary(): Observable<StatsSummary> {
    // En attendant l'API, on simule des données
    return of({
      totalSales: 1250,
      totalRevenue: 45680.50,
      totalProducts: 89,
      totalCustomers: 345,
      averageOrderValue: 36.54,
      growthPercentage: 12.5
    });
    // return this.http.get<StatsSummary>(`${this.apiUrl}/summary`);
  }

  // ===== VENTES JOURNALIÈRES =====
  
  getDailySales(days: number = 30): Observable<DailySales[]> {
    // Données simulées pour les 30 derniers jours
    const mockData: DailySales[] = [];
    const today = new Date();
    
    for (let i = days - 1; i >= 0; i--) {
      const date = new Date(today);
      date.setDate(today.getDate() - i);
      
      mockData.push({
        date: date.toISOString().split('T')[0],
        sales: Math.floor(Math.random() * 50) + 10,
        amount: Math.floor(Math.random() * 2000) + 500
      });
    }
    
    return of(mockData);
    // return this.http.get<DailySales[]>(`${this.apiUrl}/daily?days=${days}`);
  }

  // ===== VENTES MENSUELLES =====
  
  getMonthlySales(months: number = 12): Observable<MonthlySales[]> {
    const mockData: MonthlySales[] = [
      { month: 'Jan 2024', sales: 980, amount: 35420 },
      { month: 'Fév 2024', sales: 1120, amount: 40250 },
      { month: 'Mar 2024', sales: 1350, amount: 48900 },
      { month: 'Avr 2024', sales: 1200, amount: 43800 },
      { month: 'Mai 2024', sales: 1450, amount: 52100 },
      { month: 'Jun 2024', sales: 1380, amount: 49750 },
      { month: 'Jul 2024', sales: 1580, amount: 56800 },
      { month: 'Aoû 2024', sales: 1420, amount: 51200 },
      { month: 'Sep 2024', sales: 1250, amount: 45680 }
    ];
    
    return of(mockData);
    // return this.http.get<MonthlySales[]>(`${this.apiUrl}/monthly?months=${months}`);
  }

  // ===== PRODUITS LES PLUS VENDUS =====
  
  getTopSellingProducts(limit: number = 10): Observable<ProductSales[]> {
    const mockData: ProductSales[] = [
      { productName: 'Croissant Beurre', quantity: 450, revenue: 2250, percentage: 18.5 },
      { productName: 'Baguette Tradition', quantity: 380, revenue: 1520, percentage: 15.6 },
      { productName: 'Pain de Campagne', quantity: 320, revenue: 2560, percentage: 13.2 },
      { productName: 'Éclair au Chocolat', quantity: 280, revenue: 1680, percentage: 11.5 },
      { productName: 'Tarte aux Pommes', quantity: 220, revenue: 3300, percentage: 9.0 },
      { productName: 'Mille-feuille', quantity: 180, revenue: 1980, percentage: 7.4 },
      { productName: 'Chausson aux Pommes', quantity: 160, revenue: 1280, percentage: 6.6 },
      { productName: 'Pain au Chocolat', quantity: 150, revenue: 1200, percentage: 6.2 },
      { productName: 'Brioche', quantity: 140, revenue: 1820, percentage: 5.8 },
      { productName: 'Macaron', quantity: 120, revenue: 1800, percentage: 4.9 }
    ];
    
    return of(mockData.slice(0, limit));
    // return this.http.get<ProductSales[]>(`${this.apiUrl}/top-products?limit=${limit}`);
  }

  // ===== VENTES PAR CATÉGORIE =====
  
  getSalesByCategory(): Observable<CategorySales[]> {
    const mockData: CategorySales[] = [
      { categoryName: 'Viennoiseries', sales: 890, amount: 6230 },
      { categoryName: 'Pains', sales: 780, amount: 4680 },
      { categoryName: 'Pâtisseries', sales: 650, amount: 9750 },
      { categoryName: 'Gâteaux', sales: 320, amount: 6400 },
      { categoryName: 'Tartes', sales: 280, amount: 4200 },
      { categoryName: 'Chocolats', sales: 180, amount: 2700 }
    ];
    
    return of(mockData);
    // return this.http.get<CategorySales[]>(`${this.apiUrl}/by-category`);
  }

  // ===== STATISTIQUES HORAIRES =====
  
  getHourlyStats(): Observable<HourlyStats[]> {
    const mockData: HourlyStats[] = [
      { hour: '07h', sales: 15 },
      { hour: '08h', sales: 45 },
      { hour: '09h', sales: 35 },
      { hour: '10h', sales: 25 },
      { hour: '11h', sales: 30 },
      { hour: '12h', sales: 55 },
      { hour: '13h', sales: 40 },
      { hour: '14h', sales: 35 },
      { hour: '15h', sales: 30 },
      { hour: '16h', sales: 38 },
      { hour: '17h', sales: 42 },
      { hour: '18h', sales: 50 },
      { hour: '19h', sales: 25 }
    ];
    
    return of(mockData);
    // return this.http.get<HourlyStats[]>(`${this.apiUrl}/hourly`);
  }

  // ===== COMPARAISONS PÉRIODIQUES =====
  
  getComparisonData(period: 'week' | 'month' | 'quarter' = 'month'): Observable<any> {
    const mockData = {
      current: { sales: 1250, amount: 45680 },
      previous: { sales: 1120, amount: 40250 },
      growth: {
        salesGrowth: 11.6,
        amountGrowth: 13.5
      }
    };
    
    return of(mockData);
    // return this.http.get(`${this.apiUrl}/comparison?period=${period}`);
  }

  // ===== PRÉVISIONS =====
  
  getForecastData(days: number = 7): Observable<any> {
    const mockData = {
      forecast: [
        { date: '2024-09-04', expectedSales: 42, expectedAmount: 1520 },
        { date: '2024-09-05', expectedSales: 38, expectedAmount: 1380 },
        { date: '2024-09-06', expectedSales: 45, expectedAmount: 1650 },
        { date: '2024-09-07', expectedSales: 52, expectedAmount: 1890 },
        { date: '2024-09-08', expectedSales: 48, expectedAmount: 1720 },
        { date: '2024-09-09', expectedSales: 35, expectedAmount: 1250 },
        { date: '2024-09-10', expectedSales: 28, expectedAmount: 980 }
      ]
    };
    
    return of(mockData);
    // return this.http.get(`${this.apiUrl}/forecast?days=${days}`);
  }

  // ===== MÉTHODES UTILITAIRES =====
  
  exportStatistics(format: 'csv' | 'excel' = 'csv'): Observable<Blob> {
    // Implémentation de l'export
    return this.http.get(`${this.apiUrl}/export?format=${format}`, { responseType: 'blob' });
  }

  getDateRange(days: number): { start: string; end: string } {
    const end = new Date();
    const start = new Date();
    start.setDate(end.getDate() - days);
    
    return {
      start: start.toISOString().split('T')[0],
      end: end.toISOString().split('T')[0]
    };
  }
}