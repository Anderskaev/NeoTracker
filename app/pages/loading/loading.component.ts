import { Component, OnInit, AfterViewInit, ElementRef, ViewChild, inject } from '@angular/core';
import { Router } from '@angular/router';

@Component({
  selector: 'app-loading',
  standalone: true,
  imports: [],
  templateUrl: './loading.component.html',
  styleUrl: './loading.component.scss'
})
export class LoadingComponent implements OnInit, AfterViewInit {
  @ViewChild('terminal', { static: true }) terminal!: ElementRef;

  rotuer = inject(Router);

   messages = [
      {text: "> Загрузка NeoTracker OS v1.3", delay: 50, class: "success"},
      {text: "> Загрузка модулей ядра", delay: 30, class: "success"},
      {text: "[  OK  ] Mounted /proc filesystem", delay: 10, class: "complete"},
      {text: "[  OK  ] Mounted /sys filesystem", delay: 10, class: "complete"},
      {text: "[  OK  ] Инициализация системы", delay: 10, class: "complete"},
      {text: "> Запуск нейроинтерфейса...", delay: 40, class: "success"},
      {text: "[ WARN ] Нестабильное соединение", delay: 10, class: "warning"},
      {text: "> Синхронизация...", delay: 30, class: ""},
      {text: "*** Nu11 Sect0r ***", delay: 30, class: "error"},
      {text: "*** Загрузка эмулятора нейропротокола ***", delay: 30, class: "error"},
      {text: "*** Обход систем безопасности ***", delay: 30, class: "error"},
      {text: "*** Эмуляция соединения ***", delay: 30, class: "error"},
      {text: "[  OK  ] Синхронизация успешна", delay: 10, class: "success"},
      {text: "> Инициализация интерфесов", delay: 50, class: "success"},
      {text: "[  OK  ] Биомонитор", delay: 10, class: "complete"},
      {text: "[  OK  ] Трекер активности", delay: 10, class: "complete"},
      {text: "[  OK  ] Геолокация", delay: 10, class: "complete"},
      {text: "[ WARN ] Обновление протокола Synopsis", delay: 10, class: "warning"},
      {text: "[  OK  ] Загрузка данных ", delay: 50, class: "complete"},
      {text: "[  OK  ] Установка обновления", delay: 10, class: "complete"},
      {text: "> Все системы загружены", delay: 50, class: "success"},
      {text: "> Запуск NeoTracker OS v1.3", delay: 20, class: ""},
      {text: "> StartX", delay: 20, class: "", router: ['/main']},

  ];

  ngOnInit() {
    if(localStorage.getItem("silent") === "silent") {
      this.rotuer.navigate(['/main']);
    } else {
      this.displayMessages();
    }

  }

  ngAfterViewInit(): void {
    this.scrollToBottom();
  }

  displayMessages() {
    let delay = 0;
    this.messages.forEach((message, index) => {
      setTimeout(() => {
        const div = document.createElement('div');
        div.innerHTML = message.text;
        if (message.class) {
          div.className = message.class;
        }
        this.terminal.nativeElement.appendChild(div);

        if (message.router) {
          this.rotuer.navigate(message.router);
        }

        this.scrollToBottom();
      }, delay);

      delay += 100 + Math.random() * 500; // Случайные задержки между строками
    });
  }

  scrollToBottom(): void {
    this.terminal.nativeElement.scrollTop = this.terminal.nativeElement.scrollHeight;
  }

}
