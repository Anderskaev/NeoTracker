import { Component, inject, AfterViewInit } from '@angular/core';
import { RouterLink, Router } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { InputNumberModule } from 'primeng/inputnumber';
import { DataService } from '../../services/data.service';
import { MessageService } from 'primeng/api';


@Component({
  selector: 'app-data-enter',
  standalone: true,
  imports: [RouterLink, FormsModule, InputNumberModule],
  templateUrl: './data-enter.component.html',
  styleUrl: './data-enter.component.scss'
})
export class DataEnterComponent implements AfterViewInit {

  router = inject(Router);
  dataService = inject(DataService);
  messageService = inject(MessageService);

  steps: number | null = null;
  water: number | null = null;
  calories: number | null = null;

  ngAfterViewInit():void {
    this.steps = null;
    this.water = null;
    this.calories = null;
  }

  addStats(){
    let response = "";
    if(this.steps == null && this.water == null && this.calories == null) {
      response = "Хотя бы одно значение должно быть не пустым";
      this.messageService.add({ severity: 'warn', summary: "Внимание", detail: response});
      return;
    }
    let stat = {
      "telegram_id":"1",
      "steps": this.steps,
      "water": this.water,
      "calories": this.calories
    };
    this.dataService.addStats(stat).subscribe({
      next: () => {
        response = "Добавлено шагов: "+(this.steps??0)+", воды: "+(this.water??0)+", калорий: "+(this.calories??0);
        this.messageService.add({ severity: 'success', summary: "Успешно", detail: response});
        this.dataService.sendBot("tracker","/activity").subscribe({
          next: () => { } });
        this.router.navigate(['/main']);
      },
      error: () => {
        response = "Произошла ошибка...";
        this.messageService.add({ severity: 'error', summary: "Ошибка", detail: response});
      }
    });
  }

  setStats(){
    let response = "";
    if(this.steps == null && this.water == null && this.calories == null) {
      response = "Хотя бы одно значение должно быть не пустым";
      this.messageService.add({ severity: 'warn', summary: "Внимание", detail: response});
      return;
    }
    let stat = {
      "telegram_id":"1",
      "steps": this.steps,
      "water": this.water,
      "calories": this.calories
    };
    this.dataService.setStats(stat).subscribe({
      next: () => {
        response = "Установлено ";
        let res = [];
        if (this.steps != null) {
          res.push("шагов: "+(this.steps) );
        }
        if(this.water != null) {
          res.push("воды: "+(this.water));
        }
        if(this.calories != null) {
          res.push("калорий: "+(this.calories));
        }
        response += res.join(", ");
        this.messageService.add({ severity: 'success', summary: "Успешно", detail: response});
        this.dataService.sendBot("tracker","/activity").subscribe({
          next: () => { } });
        this.router.navigate(['/main']);

      },
      error: () => {
        response = "Произошла ошибка...";
        this.messageService.add({ severity: 'error', summary: "Ошибка", detail: response});
      }
    });
  }

}
