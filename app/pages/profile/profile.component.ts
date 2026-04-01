import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink, Router } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { DropdownModule } from 'primeng/dropdown';
import { InputSwitchModule } from 'primeng/inputswitch';
import { InputNumberModule } from 'primeng/inputnumber';
import { DataService } from '../../services/data.service';
import { MessageService } from 'primeng/api';
import { TelegramService } from '../../services/telegram.service';

@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [RouterLink, FormsModule, DropdownModule, InputSwitchModule, InputNumberModule, CommonModule],
  templateUrl: './profile.component.html',
  styleUrl: './profile.component.scss'
})
export class ProfileComponent implements OnInit {

  router = inject(Router);
  dataService = inject(DataService);
  messageService = inject(MessageService);
  telegram = inject(TelegramService);

  premium = 1;
  gender: string = 'Н';
  age = 0;
  weight = 0;
  height = 0;
  goal_steps = 0;
  goal_water = 0;
  goal_calories = 0;

  genders = [
    {name: 'Мужской', code: 'М'},
    {name: 'Женский', code: 'Ж'},
    {name: 'Не указывать', code: 'Н'},
  ]

  constructor(){

  }

  ngOnInit() {
    this.dataService.checkPremium().subscribe({
      next: (data) => {
        this.premium = data.premium;
      }
    });
    this.dataService.getProfile().subscribe({
      next: (profile) => {
        if(profile){
          this.gender = profile.sex;
          this.age = profile.age;
          this.weight = profile.weight;
          this.height = profile.height;
          this.goal_steps = profile.goal_steps;
          this.goal_water = profile.goal_water;
          this.goal_calories = profile.goal_calories;
        }

      }
    });
  }

  saveProfile() {
    let response = "";
    let profile = {
      sex: this.gender,
      age: this.age,
      weight: this.weight,
      height: this.height,
      goal_steps: this.goal_steps,
      goal_water: this.goal_water,
      goal_calories: this.goal_calories
    }


    this.dataService.setProfile(profile).subscribe({
      next: ()=> {
        response = "Данные сохранены!";
        this.messageService.add({ severity: 'success', summary: "Успешно", detail: response});
        this.router.navigate(['/main']);
      },
      error: () => {
        response = "Произошла ошибка...";
        this.messageService.add({ severity: 'error', summary: "Ошибка", detail: response});
      }
    });


  }



}
