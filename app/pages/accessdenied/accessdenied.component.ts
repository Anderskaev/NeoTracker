import { Component, inject } from '@angular/core';
//import { RouterLink } from '@angular/router';
import { TelegramService } from '../../services/telegram.service';
import { FormsModule } from  '@angular/forms';

@Component({
  selector: 'app-accessdenied',
  standalone: true,
  imports: [FormsModule/*RouterLink*/],
  templateUrl: './accessdenied.component.html',
  styleUrl: './accessdenied.component.scss'
})

export class AccessdeniedComponent {
  telegram = inject(TelegramService);

  closeApp() {
    console.log("Close webApp");
    this.telegram.close();
  }
}
