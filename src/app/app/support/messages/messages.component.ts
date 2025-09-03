import { Component, Input, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Message } from '../../core/chat.service';

@Component({
  selector: 'app-messages',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './messages.component.html',
  styleUrl: './messages.component.scss'
})
export class MessagesComponent implements OnInit {
  @Input() messages: Message[] = [];
  @Input() isTyping: boolean = false;

  constructor() {}

  ngOnInit(): void {}

  trackByMessageId(index: number, message: Message): number {
    return message.id;
  }

  formatMessageTime(dateString: string): string {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = now.getTime() - date.getTime();
    const diffMinutes = Math.floor(diffTime / (1000 * 60));
    const diffHours = Math.floor(diffTime / (1000 * 60 * 60));
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

    if (diffMinutes < 1) {
      return 'À l\'instant';
    } else if (diffMinutes < 60) {
      return `Il y a ${diffMinutes} min`;
    } else if (diffHours < 24) {
      return `Il y a ${diffHours}h`;
    } else if (diffDays === 1) {
      return `Hier à ${date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}`;
    } else {
      return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      });
    }
  }

  formatMessageContent(content: string): string {
    // Conversion basique du markdown en HTML
    let formattedContent = content;

    // Remplacer les liens
    formattedContent = formattedContent.replace(
      /\[([^\]]+)\]\(([^)]+)\)/g,
      '<a href="$2" target="_blank" rel="noopener noreferrer" style="color: #667eea; text-decoration: underline;">$1</a>'
    );

    // Remplacer les blocs de code
    formattedContent = formattedContent.replace(
      /```(\w+)?\n([\s\S]*?)```/g,
      '<pre><code>$2</code></pre>'
    );

    // Remplacer le code inline
    formattedContent = formattedContent.replace(
      /`([^`]+)`/g,
      '<code>$1</code>'
    );

    // Remplacer le texte en gras
    formattedContent = formattedContent.replace(
      /\*\*([^*]+)\*\*/g,
      '<strong>$1</strong>'
    );

    // Remplacer le texte en italique
    formattedContent = formattedContent.replace(
      /\*([^*]+)\*/g,
      '<em>$1</em>'
    );

    // Remplacer les listes non ordonnées
    formattedContent = formattedContent.replace(
      /^\s*[-*+]\s+(.+)$/gm,
      '<li>$1</li>'
    );

    // Entourer les listes de balises ul
    formattedContent = formattedContent.replace(
      /(<li>.*<\/li>)/gs,
      '<ul>$1</ul>'
    );

    // Remplacer les listes ordonnées
    formattedContent = formattedContent.replace(
      /^\s*\d+\.\s+(.+)$/gm,
      '<li>$1</li>'
    );

    // Entourer les listes ordonnées de balises ol
    formattedContent = formattedContent.replace(
      /(<li>.*<\/li>)/gs,
      (match) => {
        // Si ce n'est pas déjà dans une liste ul
        if (!match.includes('<ul>')) {
          return `<ol>${match}</ol>`;
        }
        return match;
      }
    );

    // Remplacer les citations
    formattedContent = formattedContent.replace(
      /^>\s+(.+)$/gm,
      '<blockquote>$1</blockquote>'
    );

    // Remplacer les sauts de ligne par des paragraphes
    const paragraphs = formattedContent.split('\n\n');
    if (paragraphs.length > 1) {
      formattedContent = paragraphs
        .filter(p => p.trim())
        .map(p => {
          // Ne pas entourer de <p> si c'est déjà un élément HTML
          if (p.trim().startsWith('<') || p.includes('<li>') || p.includes('<pre>')) {
            return p.trim();
          }
          return `<p>${p.trim().replace(/\n/g, '<br>')}</p>`;
        })
        .join('');
    } else {
      // Remplacer les simples sauts de ligne par des <br>
      formattedContent = formattedContent.replace(/\n/g, '<br>');
    }

    return formattedContent;
  }

  copyMessage(content: string): void {
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(content).then(() => {
        this.showToast('Message copié dans le presse-papier');
      }).catch(err => {
        console.error('Erreur lors de la copie:', err);
        this.fallbackCopy(content);
      });
    } else {
      this.fallbackCopy(content);
    }
  }

  private fallbackCopy(content: string): void {
    const textArea = document.createElement('textarea');
    textArea.value = content;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
      document.execCommand('copy');
      this.showToast('Message copié dans le presse-papier');
    } catch (err) {
      console.error('Erreur lors de la copie:', err);
      this.showToast('Erreur lors de la copie');
    }
    
    document.body.removeChild(textArea);
  }

  toggleLike(message: Message): void {
    if (!message.meta) {
      message.meta = {};
    }
    message.meta.liked = !message.meta.liked;
    
    // Ici, vous pourriez envoyer cette information au backend
    console.log(`Message ${message.id} ${message.meta.liked ? 'liké' : 'non liké'}`);
  }

  private showToast(message: string): void {
    // Créer un toast simple
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    toast.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      background: #48bb78;
      color: white;
      padding: 12px 16px;
      border-radius: 6px;
      font-size: 14px;
      z-index: 10000;
      opacity: 0;
      transform: translateX(100%);
      transition: all 0.3s ease;
    `;
    
    document.body.appendChild(toast);
    
    // Animation d'entrée
    setTimeout(() => {
      toast.style.opacity = '1';
      toast.style.transform = 'translateX(0)';
    }, 10);
    
    // Suppression après 3 secondes
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(100%)';
      setTimeout(() => {
        if (document.body.contains(toast)) {
          document.body.removeChild(toast);
        }
      }, 300);
    }, 3000);
  }
}