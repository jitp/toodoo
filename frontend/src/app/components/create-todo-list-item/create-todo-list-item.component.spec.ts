import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CreateTodoListItemComponent } from './create-todo-list-item.component';

describe('CreateTodoListItemComponent', () => {
  let component: CreateTodoListItemComponent;
  let fixture: ComponentFixture<CreateTodoListItemComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CreateTodoListItemComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CreateTodoListItemComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
