import { Component, ElementRef, ViewChild, AfterViewInit, OnInit, inject } from '@angular/core';
import { RouterLink, Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { DataService } from '../../services/data.service';
import { MessageService } from 'primeng/api';
import { DividerModule } from 'primeng/divider';

@Component({
  selector: 'app-main',
  standalone: true,
  imports: [RouterLink, DividerModule, CommonModule],
  templateUrl: './main.component.html',
  styleUrls: ['./main.component.scss']
})

export class MainComponent implements AfterViewInit, OnInit {
  @ViewChild('consoleOutput') consoleOutput!: ElementRef;
  @ViewChild('consoleInput') consoleInput!: ElementRef;

  dataService = inject(DataService);
  messageService = inject(MessageService);
  router = inject(Router);
  
  premium = 1;

  steps = 0;
  water = 0;
  calories = 0;
  mission = {
    start_date: "",
    title: "[Ошибка системы] Миссия не найдена",
    trigger_type: "",
    days: 0,
    steps: 0,
    water: 0,
    cal: 500000,
    inrow: 1,
    duedate:""
  };


  getRandomInt(min: number, max: number) {
    min = Math.ceil(min);
    max = Math.floor(max);
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

  ngAfterViewInit() {
    // Фокус на input при загрузке.
   // this.consoleInput.nativeElement.focus();
  }

  ngOnInit() {

    this.dataService.checkPremium().subscribe({
      next: (data) => {
        this.premium = data.premium;
      }
    });

    this.dataService.getStats("today").subscribe({
      next: (stat)=>{
        this.steps = stat.stats.steps ?? 0;
        this.water = stat.stats.water ?? 0;
        this.calories = stat.stats.calories ?? 0;
      }
    });

    this.dataService.getMissionReq().subscribe({
      next: (req)=>{
        if(req.requirements) {
          this.mission = req.requirements;
        }
        //console.log(req);
      }
    });

  }

  msg() {
    let detail = "";

    if(this.mission.trigger_type == "activity") {
      detail += "Миссия запускается после ввода данных.\n";
    } else if (this.mission.trigger_type == "cron"){
      detail += "Миссия запускается по расписанию.\n";
    } else if (this.mission.trigger_type  == "signal") {
      detail += "Миссия запускается автоматически.\n";
    }


    if(this.mission.inrow>1 && ((this.mission.steps>0) || (this.mission.water>0) || (this.mission.cal<500000))) {
      detail += "Для этого на протяжении <b>"+this.mission.inrow+" дней <i>подряд</i></b> нужно: <br/>";
    } else if (this.mission.inrow>1) {
      detail += "Для этого на протяжении <b>"+this.mission.inrow+" дней <i>подряд</i></b> нужно вводить любые данные.<br/>";
    } else if(this.mission.days>0 && ((this.mission.steps>0) || (this.mission.water>0) || (this.mission.cal<500000))) {
      detail += "Для этого <b>"+this.mission.days+"</b> дней (допустимы перерывы) нужно: <br/>";
    } else {
      detail += "Для этого ничего не нужно.\n";
    }

    if(this.mission.steps>0) {
      detail += " - проходить <b>не менее "+this.mission.steps+"</b> шага(ов).<br/>";
    }
    if(this.mission.water>0) {
      detail += " - выпивать <b>не менее "+this.mission.water+"</b> мл воды.<br/>";
    }
    if(this.mission.cal<500000) {
      detail += " - съедать <b>не более "+this.mission.cal+"</b> ККАЛ.<br/>";
    }


   /* steps: 0,
    water: 0,
    cal: 0,
    inrow: 1,
    duedate:""*/
    this.messageService.add({ severity: 'info', summary: 'Подсказка', detail: detail, sticky: true, key: 'cnt' });
  }

  handleCommand(event: KeyboardEvent) {
    if (event.key === 'Enter') {
      const input = event.target as HTMLInputElement;
      const cmds = input.value.trim().split(' ');
      const command = cmds[0];


      if (command !== '') {
        this.addConsoleCmd('> ' + cmds.join(' '));

        // Обработка команд
        let response = '';
        if (command === '/show') {
          if(cmds.length<2 || (['today','week','month','all'].indexOf(cmds[1]) == -1)) {
            response = 'Использование: /show today|week|month|all. Более подробно смотри /help';
            this.addConsoleLine(response);
            input.value = '';
          } else {
            this.dataService.getStats(cmds[1]).subscribe({
              next: (data)=> {
                let sr = (100+this.getRandomInt(5,25))/100;
                let wr = (100+this.getRandomInt(5,25))/100;
                let cr = (100-this.getRandomInt(5,15))/100;
                response = "Данные за запрашиваемый период. Шаги: "+Math.floor((data.stats.steps??0)*sr)+", вода: "+Math.floor((data.stats.water??0)*wr)+", калории: "+Math.floor((data.stats.calories??0)*cr);
                this.addConsoleLine(response);
                this.addConsoleLine("[ВНИМАНИЕ] Выявлено искажение данных. Используйте команду /getstat");
                input.value = '';
              }
            });
          }
          //response = 'Шагов: 4200, Воды: 1.2л, Калории: 1100.';
        } else if (command === '/getstat') {
          if(cmds.length<2 || (['today','week','month','all'].indexOf(cmds[1]) == -1)) {
            response = 'Использование: /getstat today|week|month|all. Более подробно смотри /help';
            this.addConsoleLine(response);
            input.value = '';
          } else {
            this.dataService.getStats(cmds[1]).subscribe({
              next: (data)=> {
                response = "Данные за запрашиваемый период. Шаги: "+(data.stats.steps??0)+", вода: "+(data.stats.water??0)+", калории: "+(data.stats.calories??0);
                this.addConsoleLine(response);
                input.value = '';
              }
            });
          }
        } else if (command === '/addall') {
            if(cmds.length<4 || !( !isNaN(+cmds[1]) && Number.isInteger(+cmds[1])) || !( !isNaN(+cmds[2]) && Number.isInteger(+cmds[2]))|| !( !isNaN(+cmds[3]) && Number.isInteger(+cmds[3])) ) {
              response = "Использование: /addall <количество шагов> <количество воды> <количество калорий>. Более подробно смотри /help";
              this.addConsoleLine(response);
              input.value = '';
            } else {
              let stat = {
                    "telegram_id":"1",
                    "steps": cmds[1],
                    "water": cmds[2],
                    "calories": cmds[3],
              };
              this.dataService.addStats(stat).subscribe({
                next: () => {
                  response = "Добавлено шагов: "+cmds[1]+", воды: "+cmds[2]+", калорий: "+cmds[3];
                  this.steps = +this.steps+ +cmds[1];
                  this.water = +this.water+ +cmds[2];
                  this.calories = +this.calories+ +cmds[3];
                  this.addConsoleLine(response);
                  input.value = '';
                },
                error: () => {
                  response = "Произошла ошибка...";
                  this.addConsoleLine(response);
                  input.value = '';
                }
              });
            }

        } else if (command === '/setall') {
          if(cmds.length<4 || !( !isNaN(+cmds[1]) && Number.isInteger(+cmds[1])) || !( !isNaN(+cmds[2]) && Number.isInteger(+cmds[2]))|| !( !isNaN(+cmds[3]) && Number.isInteger(+cmds[3])) ) {
            response = "Использование: /setall <количество шагов> <количество воды> <количество калорий>. Более подробно смотри /help";
            this.addConsoleLine(response);
            input.value = '';
          } else {
            let stat = {
                  "telegram_id":"1",
                  "steps": cmds[1],
                  "water": cmds[2],
                  "calories": cmds[3],
            };
            this.dataService.setStats(stat).subscribe({
              next: () => {
                console.log('сработка');
                response = "Установлено шагов: "+cmds[1]+", воды: "+cmds[2]+", калорий: "+cmds[3];
                this.steps = +cmds[1];
                this.water = +cmds[2];
                this.calories = +cmds[3];
                this.addConsoleLine(response);
                input.value = '';
              },
              error: () => {
                response = "Произошла ошибка...";
                this.addConsoleLine(response);
                input.value = '';
              }
            });
          }

      } else if(command === '/hack') {
          response = `Hacker's terminal v.12.4
          *******
          <i>Нечего взламывать</i>
          *******
          Powered by Nu11 Sect0r`;
          this.addConsoleLine(response);
          input.value = '';
      } else if(command === '/help') {
        response = `Справка
<b>/help</b> Для отображения справки по компандам.
<b>/addall</b> <i>&lt;кол-во шагов&gt; &lt;кол-во воды&gt; &lt;кол-во ККАЛ&gt;</i> Добавляет указанное количество к соотвествующим показателям за день.
    <b>Пример:</b>
    &gt;/addall 10000 200 100
    Добавляет к показателям 10000 шагов, 200мл воды и 100 ККАЛ за день.

<b>/setall</b> <i>&lt;кол-во шагов&gt; &lt;кол-во воды&gt; &lt;кол-во ККАЛ&gt;</i> Устанавливает соотвествующие показатели равным указанному количеству за день.
    <b>Пример:</b>
    &gt;/setall 10000 200 100
    Устанавивает показатели равные 10000 шагов, 200мл воды и 100 ККАЛ за день.

<b>/show</b> <i>today|week|month|all</i> Отображает данные за выбранный период.
    <i>today</i> - Данные за сегодня
    <i>week</i> - Данные за неделю
    <i>month</i> - Данные за месяц
    <i>all</i> - Данные за весь период
    <b>Пример:</b>
    &gt;/show week
    Выводит данные показателей за текущую неделю.`;
        this.addConsoleLine(response);
        input.value = '';
      } else if (command === '/exit') {
        localStorage.setItem("access", "denied");
        this.router.navigate(['/login']);
      }
        else {
          response = 'Неизвестная команда: ' + command;
          input.value = '';
        }

      }
    }
  }

  private addConsoleCmd(text: string) {
    const line = document.createElement('div');
    line.className = 'console-line';
    line.textContent = text;
    this.consoleOutput.nativeElement.appendChild(line);
    this.consoleOutput.nativeElement.scrollTop = this.consoleOutput.nativeElement.scrollHeight;
  }

  private addConsoleLine(text: string) {
    const line = document.createElement('div');
    line.className = 'console-line';
    line.innerHTML = text;
    this.consoleOutput.nativeElement.appendChild(line);
    this.consoleOutput.nativeElement.scrollTop = this.consoleOutput.nativeElement.scrollHeight;
  }

}
