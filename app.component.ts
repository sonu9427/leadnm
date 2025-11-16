import { Component, OnInit } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { FormBuilder, FormGroup } from '@angular/forms';
// import { ToastrService } from 'ngx-toastr';
import { ReactiveFormsModule } from '@angular/forms';
import { MyService } from './services/my.service';
import { CommonModule } from '@angular/common';
//import * as $ from 'jquery';
//import $, { error } from 'jquery';

@Component({
  selector: 'app-root',
  imports: [ReactiveFormsModule, CommonModule],
  templateUrl: './app.component.html',
  styleUrl: './app.component.scss',
})
export class AppComponent implements OnInit {
  title = 'frontend';
  addForm: FormGroup;
  users: any[] = [];
  showdivDisabled = false;
  //showdiv;

  constructor(private fb: FormBuilder, private myService: MyService) {
    this.addForm = this.fb.group({
      name: [''],
      mobile: [''],
    });
  }

  ngOnInit() {
    this.loadUsers();
  }

  loadUsers() {
    this.myService.getUsers().subscribe({
      next: (res: any) => {
        this.users = res.user;
        //console.log('testme', this.users);
        // const checkedUser = this.users.find((u) => u.is_checked === 'Yes');
        // alert(checkedUser);
        // this.showdivDisabled = !!checkedUser; // true if found
        console.log('Div disabled status: ', this.showdivDisabled);
      },
      error: (err) => {
        console.error('Error loading users:', err);
      },
    });
  }

  // ...existing code...
  selectedUsers: any[] = [];

  onCheckboxChange(event: any, user: any) {
    if (event.target.checked) {
      this.selectedUsers.push(user.id);
    } else {
      this.selectedUsers = this.selectedUsers.filter((id) => id !== user.id);
    }
  }

  updateChk() {
    console.log('Checked users:', this.selectedUsers);
    const id = this.selectedUsers.join(',');

    this.myService.updateChecked(id).subscribe({
      next: (res: any) => {
        console.log('Success:', res);
        // this.successMessage = (res as any).message;
        this.addForm.reset();
        this.loadUsers();
        // this.closeModal();
      },
      error: (err) => {
        console.error('Error:', err);
      },
    });
  }
  // ...existing code...

  addUser() {
    const name = this.addForm.value.name;
    const mobile = this.addForm.value.mobile;
    this.myService.register(name, mobile).subscribe({
      next: (res: any) => {
        console.log('Success:', res);
        // this.successMessage = (res as any).message;
        this.addForm.reset();
        this.loadUsers();
        // this.closeModal();
      },
      error: (err) => {
        console.error('Error:', err);
      },
    });
  }
}
