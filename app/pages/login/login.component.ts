import { Component, inject } from '@angular/core';
import { TelegramService } from '../../services/telegram.service';
import { FormsModule } from "@angular/forms";
import { Router } from '@angular/router';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [FormsModule],
  templateUrl: './login.component.html',
  styleUrl: './login.component.scss'
})
export class LoginComponent {
  telegram = inject(TelegramService);
  router = inject(Router);
  test = "";
  pass = "";

  constructor() {
    //k = this.telegram.setItem("key1", 100);
    this.test = this.telegram.version() || "0";
    localStorage.setItem("access", "denied");
    //k = this.telegram.getItem("key1");
    //console.log(k);
  }

  login() {
    if(this.pass == "свобода") {
      localStorage.setItem("access", "granted");
      this.router.navigate(['/main']);
    } else {
      this.router.navigate(['/accessdenied']);
    }
  }

}
