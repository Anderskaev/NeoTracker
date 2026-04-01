import { Component, OnInit, inject } from '@angular/core';
import { RouterLink, Router } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { DropdownModule } from 'primeng/dropdown';
import { InputSwitchModule } from 'primeng/inputswitch';
import { DataService } from '../../services/data.service';
import { MessageService } from 'primeng/api';

@Component({
  selector: 'app-settings',
  standalone: true,
  imports: [RouterLink, FormsModule, DropdownModule, InputSwitchModule],
  templateUrl: './settings.component.html',
  styleUrl: './settings.component.scss'
})
export class SettingsComponent implements OnInit {

  router = inject(Router);
  dataService = inject(DataService);
  messageService = inject(MessageService);

  silent = ""
  timezone: number = 3;
  timeremind = "13:00";
  utc: any = [];
  time: any = [];
  time_switch = 0;

  constructor(){
    for (let i = -12; i <= 12; i = i + 1) {
      let u = i < 0 ? i.toString() : "+" + i.toString();

      let u_itm = {
          code: i,
          name: 'UTC' + u
      };

      this.utc.push(u_itm);
  }

    for (let i = 0; i<=23; i = i + 1) {
      let t_itm = {
        code: i.toString().padStart(2,'0')+':00',
        name: i.toString().padStart(2,'0')+':00'
      }
      this.time.push(t_itm);
    }
  }

  ngOnInit() {
    this.silent = localStorage.getItem("silent")??"not";
    this.dataService.getSettings().subscribe({
      next: (sett) => {
        this.timezone = Number(sett.GMT);
        console.log(this.timezone);
        this.time_switch = sett.notification;
        this.timeremind = sett.notification_time;
      }
    });
  }

  saveSettings() {
    let response = "";
    let settings = {
      GMT: this.timezone,
      notification: this.time_switch,
      notification_time: this.timeremind
    }


    this.dataService.setSettings(settings).subscribe({
      next: ()=> {
        response = "Настройки сохранены!";
        this.messageService.add({ severity: 'success', summary: "Успешно", detail: response});
        localStorage.setItem("silent", this.silent);
        this.router.navigate(['/main']);
      },
      error: () => {
        response = "Произошла ошибка...";
        this.messageService.add({ severity: 'error', summary: "Ошибка", detail: response});
      }
    });


  }

}
