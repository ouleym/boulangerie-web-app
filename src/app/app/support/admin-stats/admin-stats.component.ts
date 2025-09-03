import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Subject, takeUntil, forkJoin } from 'rxjs';

// Import du service
import { 
  StatisticsService, 
  StatsSummary, 
  DailySales, 
  MonthlySales, 
  ProductSales, 
  CategorySales,
  HourlyStats
} from '../../core/statistics.service';

// Types pour Chart.js
interface ChartDataset {
  label: string;
  data: number[];
  backgroundColor?: string | string[];
  borderColor?: string;
  borderWidth?: number;
  fill?: boolean;
}

interface ChartData {
  labels: string[];
  datasets: ChartDataset[];
}

@Component({
  selector: 'app-admin-stats',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './admin-stats.component.html',
  styleUrl: './admin-stats.component.scss'
})
export class AdminStatsComponent implements OnInit, OnDestroy {
  private destroy$ = new Subject<void>();

  // ===== DONNÉES =====
  statsSummary: StatsSummary | null = null;
  dailySales: DailySales[] = [];
  monthlySales: MonthlySales[] = [];
  topProducts: ProductSales[] = [];
  categoryStats: CategorySales[] = [];
  hourlyStats: HourlyStats[] = [];

  // ===== ÉTAT DE L'INTERFACE =====
  isLoading = true;
  selectedPeriod = '30'; // jours
  activeTab = 'overview';
  
  // ===== DONNÉES POUR LES GRAPHIQUES =====
  dailySalesChart: ChartData = { labels: [], datasets: [] };
  monthlySalesChart: ChartData = { labels: [], datasets: [] };
  topProductsChart: ChartData = { labels: [], datasets: [] };
  categoryChart: ChartData = { labels: [], datasets: [] };
  hourlyChart: ChartData = { labels: [], datasets: [] };

  // ===== OPTIONS POUR LES PÉRIODES =====
  periodOptions = [
    { value: '7', label: '7 derniers jours' },
    { value: '30', label: '30 derniers jours' },
    { value: '90', label: '3 derniers mois' },
    { value: '365', label: '12 derniers mois' }
  ];

  constructor(private statisticsService: StatisticsService) {}

  ngOnInit() {
    this.loadAllStatistics();
  }

  ngOnDestroy() {
    this.destroy$.next();
    this.destroy$.complete();
  }

  // ===== CHARGEMENT DES DONNÉES =====
  
  loadAllStatistics() {
    this.isLoading = true;
    
    forkJoin({
      summary: this.statisticsService.getStatsSummary(),
      dailySales: this.statisticsService.getDailySales(parseInt(this.selectedPeriod)),
      monthlySales: this.statisticsService.getMonthlySales(12),
      topProducts: this.statisticsService.getTopSellingProducts(10),
      categoryStats: this.statisticsService.getSalesByCategory(),
      hourlyStats: this.statisticsService.getHourlyStats()
    }).pipe(
      takeUntil(this.destroy$)
    ).subscribe({
      next: (data) => {
        this.statsSummary = data.summary;
        this.dailySales = data.dailySales;
        this.monthlySales = data.monthlySales;
        this.topProducts = data.topProducts;
        this.categoryStats = data.categoryStats;
        this.hourlyStats = data.hourlyStats;
        
        this.prepareChartData();
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Erreur lors du chargement des statistiques:', error);
        this.isLoading = false;
      }
    });
  }

  // ===== PRÉPARATION DES DONNÉES POUR LES GRAPHIQUES =====
  
  prepareChartData() {
    this.prepareDailySalesChart();
    this.prepareMonthlySalesChart();
    this.prepareTopProductsChart();
    this.prepareCategoryChart();
    this.prepareHourlyChart();
  }

  prepareDailySalesChart() {
    const labels = this.dailySales.map(item => {
      const date = new Date(item.date);
      return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' });
    });
    
    this.dailySalesChart = {
      labels,
      datasets: [
        {
          label: 'Nombre de ventes',
          data: this.dailySales.map(item => item.sales),
          borderColor: '#007bff',
          backgroundColor: 'rgba(0, 123, 255, 0.1)',
          borderWidth: 2,
          fill: true
        },
        {
          label: 'Montant (€)',
          data: this.dailySales.map(item => item.amount),
          borderColor: '#28a745',
          backgroundColor: 'rgba(40, 167, 69, 0.1)',
          borderWidth: 2,
          fill: true
        }
      ]
    };
  }

  prepareMonthlySalesChart() {
    this.monthlySalesChart = {
      labels: this.monthlySales.map(item => item.month),
      datasets: [
        {
          label: 'Ventes mensuelles',
          data: this.monthlySales.map(item => item.sales),
          backgroundColor: [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
            '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF',
            '#4BC0C0', '#FF6384', '#36A2EB', '#FFCE56'
          ],
          borderWidth: 1
        }
      ]
    };
  }

  prepareTopProductsChart() {
    this.topProductsChart = {
      labels: this.topProducts.map(item => item.productName),
      datasets: [
        {
          label: 'Quantité vendue',
          data: this.topProducts.map(item => item.quantity),
          backgroundColor: [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#36A2EB'
          ]
        }
      ]
    };
  }

  prepareCategoryChart() {
    this.categoryChart = {
      labels: this.categoryStats.map(item => item.categoryName),
      datasets: [
        {
          label: 'Chiffre d\'affaires (€)',
          data: this.categoryStats.map(item => item.amount),
          backgroundColor: [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
          ]
        }
      ]
    };
  }

  prepareHourlyChart() {
    this.hourlyChart = {
      labels: this.hourlyStats.map(item => item.hour),
      datasets: [
        {
          label: 'Ventes par heure',
          data: this.hourlyStats.map(item => item.sales),
          backgroundColor: 'rgba(54, 162, 235, 0.6)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 2
        }
      ]
    };
  }

  // ===== GESTION DES ÉVÉNEMENTS =====
  
  onPeriodChange() {
    this.loadAllStatistics();
  }

  onTabChange(tab: string) {
    this.activeTab = tab;
  }

  // ===== MÉTHODES UTILITAIRES =====
  
  formatCurrency(amount: number): string {
    return new Intl.NumberFormat('fr-FR', {
      style: 'currency',
      currency: 'EUR'
    }).format(amount);
  }

  formatNumber(num: number): string {
    return new Intl.NumberFormat('fr-FR').format(num);
  }

  formatPercentage(percentage: number): string {
    const sign = percentage >= 0 ? '+' : '';
    return `${sign}${percentage.toFixed(1)}%`;
  }

  getGrowthClass(percentage: number): string {
    return percentage >= 0 ? 'text-success' : 'text-danger';
  }

  getGrowthIcon(percentage: number): string {
    return percentage >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down';
  }

  // ===== EXPORT DES DONNÉES =====
  
  exportStatistics(format: 'csv' | 'excel') {
    this.statisticsService.exportStatistics(format)
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: (blob: Blob | MediaSource) => {
          const url = window.URL.createObjectURL(blob);
          const link = document.createElement('a');
          link.href = url;
          link.download = `statistiques-${new Date().toISOString().split('T')[0]}.${format}`;
          link.click();
          window.URL.revokeObjectURL(url);
        },
        error: (error: any) => {
          console.error('Erreur lors de l\'export:', error);
        }
      });
  }

  getCategoryColor(index: number): string {
  const colors = [
    '#FF6384', '#36A2EB', '#FFCE56', 
    '#4BC0C0', '#9966FF', '#FF9F40'
  ];
  return colors[index % colors.length];
}

// Données hebdomadaires pour la timeline (vue temporal)
getWeeklyData() {
  if (!this.dailySales || this.dailySales.length === 0) {
    return [];
  }

  const last7Days = this.dailySales.slice(-7); // prend les 7 derniers jours
  const totalAmount = last7Days.reduce((sum, d) => sum + d.amount, 0);

  return last7Days.map(d => {
    const date = new Date(d.date);
    return {
      name: date.toLocaleDateString('fr-FR', { weekday: 'short' }),
      date: date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' }),
      sales: this.formatNumber(d.sales),
      amount: d.amount,
      percentage: totalAmount > 0 ? (d.amount / totalAmount) * 100 : 0
    };
  });
}
}