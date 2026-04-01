import { Component, inject, OnInit } from '@angular/core';
import { ChartModule } from 'primeng/chart';
import { Chart } from 'chart.js';
import { RouterLink } from '@angular/router';
import annotationPlugin from 'chartjs-plugin-annotation';
import { SelectButtonModule } from 'primeng/selectbutton';
import { FormsModule } from '@angular/forms';
import { DataService } from '../../services/data.service';

@Component({
  selector: 'app-stats',
  standalone: true,
  imports: [ChartModule, RouterLink, SelectButtonModule, FormsModule],
  templateUrl: './stats.component.html',
  styleUrl: './stats.component.scss'
})
export class StatsComponent implements OnInit {

  dataService = inject(DataService);

  Options: any;
  stepsGraphData: any;  //переменная для графика
  waterGraphData: any; //переменная для графика
  calGraphData: any; //переменная для графика

  stepsGraphDataPrep: any = [{'daily': {}}, {'weekly': {}},{'monthly': {}}];  //подгтовленные данные
  waterGraphDataPrep: any = [{'daily': {}}, {'weekly': {}},{'monthly': {}}];; //подгтовленные данные
  calGraphDataPrep: any = [{'daily': {}}, {'weekly': {}},{'monthly': {}}];; //подгтовленные данные


  dailyData:  any[] = []; //из запроса
  weeklyData: any[] = []; //из запроса
  monthlyData: any[] = []; //из запроса

  weekLabels = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
  monthLabels = []; //генерировать в map?
  yearLabels = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];

  monthWeeks: number[] = [];


  graphType = 1;
  graphTypes = [
    {name: 'Неделя', value: 1},
    {name: 'Месяц', value: 2},
    {name: 'Год', value: 3},
  ];


  getWeekNumbersOfCurrentMonth(): number[] {
    const now = new Date();
    const currentYear = now.getFullYear();
    const currentMonth = now.getMonth();

    // Получаем первый и последний день месяца
    const firstDayOfMonth = new Date(currentYear, currentMonth, 1);
    const lastDayOfMonth = new Date(currentYear, currentMonth + 1, 0);

    const weekNumbers = new Set<number>();

    // Функция для получения номера недели по ISO
    const getWeekNumber = (date: Date): number => {
      const tempDate = new Date(date);
      tempDate.setHours(0, 0, 0, 0);
      tempDate.setDate(tempDate.getDate() + 3 - (tempDate.getDay() + 6) % 7);
      const week1 = new Date(tempDate.getFullYear(), 0, 4);
      return 1 + Math.round(((tempDate.getTime() - week1.getTime()) / 86400000 - 3 + (week1.getDay() + 6) % 7) / 7);
    };

    // Перебираем все дни месяца и собираем номера недель
    for (let day = firstDayOfMonth; day <= lastDayOfMonth; day.setDate(day.getDate() + 1)) {
      const weekNumber = getWeekNumber(new Date(day));
      weekNumbers.add(weekNumber);
    }

    return Array.from(weekNumbers).sort((a, b) => a - b);
  }


  ngOnInit() {

    const namedChartAnnotation = annotationPlugin;
    Chart.register( namedChartAnnotation);
    this.Options = this.makeOptions();
    this.monthWeeks = this.getWeekNumbersOfCurrentMonth();

    this.dataService.getGraph().subscribe({
      next: (data) => {
        this.dailyData = data.data.daily;
        this.weeklyData = data.data.weekly;
        this.monthlyData = data.data.monthly;
        this.prepareDailyValues();
        this.prepareWeeklyValues();
        this.prepareMonthlyValues();
        this.changeValues();
      }
    });
    //subscribe to getstat///





  }

  makeOptions() {
    return {
      responsive: true,
      plugins: {
        legend: {
          'onClick': '',
          labels: {
            color: '#00ffe0'
          }
        },
        annotation: {
          annotations: [{
            // drawTime: 'afterDatasetsDraw',
            id: 'hline',
            type: 'line',
            mode: 'horizontal',
            scaleID: 'y',
            value: 0,

            borderWidth: 2
            }]
          },
      },
      scales: {
        x: {
          ticks: {
            color: '#00ffe0'
          },
          grid: {
            color: 'rgba(0, 255, 174, 0.1)'
          }
        },
        y: {
          beginAtZero: true,
          ticks: {
            precision: 0,
            color: '#00ffe0'
          },
          grid: {
            color: 'rgba(0, 255, 174, 0.1)'
          }
        }
      }
    }
  } //end func makeOptions

  prepareDailyValues() {
    let stepsData = Array(7).fill(0);
    let waterData = Array(7).fill(0);
    let calData = Array(7).fill(0);
    let labels = this.weekLabels;

    Object.entries(this.dailyData).forEach(([day, metrics]) => {
      const dayIndex = parseInt(day) - 1; // "2" → 1
      if (dayIndex >= 0 && dayIndex < 7) {
        stepsData[dayIndex] = metrics.steps;
        waterData[dayIndex] = metrics.water;
        calData[dayIndex] = metrics.calories;
      }
    });


    this.stepsGraphDataPrep['daily'] = {
      labels: labels,
      datasets: [{
        label: 'Шаги',
        data: stepsData,
        borderColor: '#00ffe0',
        //backgroundColor: '#00ffe0',
        backgroundColor: 'rgba(0, 255, 174, 0.4)',
        borderRadius: 4,
        tension: 0.3
      }]
    }
    this.waterGraphDataPrep['daily'] = {
      labels: labels,
      datasets: [{
        label: 'Вода',
        data: waterData,
        borderColor: '#00ffe0',
        //backgroundColor: '#00ffe0',
        backgroundColor: 'rgba(0, 255, 174, 0.4)',
        borderRadius: 4,
        tension: 0.3
      }]
    }
    this.calGraphDataPrep['daily'] = {
      labels: labels,
      datasets: [{
        label: 'Калории',
        data: calData,
        borderColor: '#00ffe0',
        //backgroundColor: '#00ffe0',
        backgroundColor: 'rgba(0, 255, 174, 0.4)',
        borderRadius: 4,
        tension: 0.3
      }]
    }
  } //end prepare daily

  prepareWeeklyValues() {

    let stepsData = Array(this.monthWeeks.length).fill(0);
    let waterData = Array(this.monthWeeks.length).fill(0);
    let calData = Array(this.monthWeeks.length).fill(0);

    let labels: string[] = this.monthLabels;
    this.monthWeeks.map((weekNum, index) => {
      labels.push('Нед.'+(index+1).toString());
      stepsData[index] = this.weeklyData[weekNum]?.steps || 0;
      waterData[index] = this.weeklyData[weekNum]?.water || 0;
      calData[index] = this.weeklyData[weekNum]?.calories || 0;
    });

    this.stepsGraphDataPrep['weekly'] = {
      labels: labels,
      datasets: [{
        label: 'Шаги',
        data: stepsData,
        borderColor: '#00ffe0',
        //backgroundColor: '#00ffe0',
        backgroundColor: 'rgba(0, 255, 174, 0.4)',
        borderRadius: 4,
        tension: 0.3
      }]
    }
    this.waterGraphDataPrep['weekly'] = {
      labels: labels,
      datasets: [{
        label: 'Вода',
        data: waterData,
        borderColor: '#00ffe0',
        //backgroundColor: '#00ffe0',
        backgroundColor: 'rgba(0, 255, 174, 0.4)',
        borderRadius: 4,
        tension: 0.3
      }]
    }
    this.calGraphDataPrep['weekly'] = {
      labels: labels,
      datasets: [{
        label: 'Калории',
        data: calData,
        borderColor: '#00ffe0',
        //backgroundColor: '#00ffe0',
        backgroundColor: 'rgba(0, 255, 174, 0.4)',
        borderRadius: 4,
        tension: 0.3
      }]
    }
  } //end prepare monthly

  prepareMonthlyValues() {
    let stepsData = Array(12).fill(0);
    let waterData = Array(12).fill(0);
    let calData = Array(12).fill(0);
    let labels = this.yearLabels;

    Object.entries(this.monthlyData).forEach(([day, metrics]) => {
      const dayIndex = parseInt(day) - 1; // "2" → 1
      if (dayIndex >= 0 && dayIndex < 7) {
        stepsData[dayIndex] = metrics.steps;
        waterData[dayIndex] = metrics.water;
        calData[dayIndex] = metrics.calories;
      }
    });


    this.stepsGraphDataPrep['monthly'] = {
      labels: labels,
      datasets: [{
        label: 'Шаги',
        data: stepsData,
        borderColor: '#00ffe0',
        //backgroundColor: '#00ffe0',
        backgroundColor: 'rgba(0, 255, 174, 0.4)',
        borderRadius: 4,
        tension: 0.3
      }]
    }
    this.waterGraphDataPrep['monthly'] = {
      labels: labels,
      datasets: [{
        label: 'Вода',
        data: waterData,
        borderColor: '#00ffe0',
        //backgroundColor: '#00ffe0',
        backgroundColor: 'rgba(0, 255, 174, 0.4)',
        borderRadius: 4,
        tension: 0.3
      }]
    }
    this.calGraphDataPrep['monthly'] = {
      labels: labels,
      datasets: [{
        label: 'Калории',
        data: calData,
        borderColor: '#00ffe0',
        //backgroundColor: '#00ffe0',
        backgroundColor: 'rgba(0, 255, 174, 0.4)',
        borderRadius: 4,
        tension: 0.3
      }]
    }
  } //end prepare daily

  changeValues(){

    switch(this.graphType) {
      case 1:
        this.stepsGraphData = this.stepsGraphDataPrep['daily'];
        this.waterGraphData = this.waterGraphDataPrep['daily'];
        this.calGraphData = this.calGraphDataPrep['daily'];
      break;
      case 2:
        this.stepsGraphData = this.stepsGraphDataPrep['weekly'];
        this.waterGraphData = this.waterGraphDataPrep['weekly'];
        this.calGraphData = this.calGraphDataPrep['weekly'];
      break;
      case 3:

        this.stepsGraphData = this.stepsGraphDataPrep['monthly'];
        this.waterGraphData = this.waterGraphDataPrep['monthly'];
        this.calGraphData = this.calGraphDataPrep['monthly'];
       // this.GraphData = this.GraphData3;
      break;
    }

  }

}
