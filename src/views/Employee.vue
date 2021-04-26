
<template>
  <div class="home">
    <div class="form">
      <form action="#">
        <h1 class="center">Add Employee</h1>
        <br />
        <div>
          <label for="fname">First name:</label>
          <input id="fname" type="text" v-model="newEmployee.fname" required />
        </div>

        <div>
          <label> Last name:</label>
          <input id="input" type="text" v-model="newEmployee.lname" required />
        </div>

        <div>
          <label>email:</label>
          <input id="input" type="text" v-model="newEmployee.email" required />
        </div>

        <div>
          <label>job:</label>

          <select v-model="newEmployee.job_id" required>
            <option v-for="job in jobs" :value="job.job_id"
              >{{ job.name }}
            </option>
          </select>
        </div>

        <button class="btn" v-on:click="createEmployee(newEmployee)">
          Create
        </button>
      </form>
    </div>
    <br />
    <table>
      <thead>
        <th>First name</th>
        <th>Last name</th>
        <th>Email</th>
        <th>Job</th>
        <th>&nbsp;&nbsp;&nbsp;&nbsp;</th>
        <th>&nbsp;&nbsp;&nbsp;&nbsp;</th>
      </thead>
      <tbody>
        <tr v-for="employee in employees">
          <td>{{ employee.fname }}</td>
          <td>{{ employee.lname }}</td>
          <td>{{ employee.email }}</td>
          <td>{{ employee.name }}</td>
          <td><button v-on:click="getScores(employee)">scores</button></td>
          <dialog id="recipe-detail">
            <form method="dialog">
              <button class="btb">Close</button>
              <table id="inner-table">
                <thead>
                  <th>Competency</th>
                  <th>Score</th>
                </thead>
                <tbody>
                  <tr v-for="score in scores">
                    <td>{{ score.name }}</td>
                    <td>{{ score.score }}</td>
                  </tr>
                </tbody>
              </table>
            </form>
          </dialog>
          <td>
            <button v-on:click="showForm(employee)">Edit</button>
          </td>
          <dialog id="form-edit">
            <form method="dialog">
              <div class="form">
                <div>
                  <label> First name:</label>
                  <input type="text" v-model="currentEmployee.fname" required />
                </div>

                <div>
                  <label>
                    Last name:
                    <input
                      type="text"
                      v-model="currentEmployee.lname"
                      required
                    />
                  </label>
                </div>

                <div>
                  <label>
                    email:
                    <input type="text" v-model="currentEmployee.email" required
                  /></label>
                </div>

                <div>
                  <label> job: </label>
                  <select required v-model="currentEmployee.job_id">
                    <option :value="job.job_id" v-for="job in jobs">
                      {{ job.name }}</option
                    >
                  </select>
                </div>

                <button class="btn" v-on:click="updateEmployee(currentEmployee)">
                  Update
                </button>
                <button class="btn" v-on:click="deleteEmployee(currentEmployee)">
                  Destroy
                </button>
                <button class="btn">Close</button>
              </div>
            </form>
          </dialog>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style></style>

<script>
var axios = require("axios");

export default {
  data: function() {
    return {
      message: "Welcome to Vue.js!",
      employees: [],
      url: "http://localhost/project2/data_p2.php",
      newEmployee: {},
      currentEmployee: {},
      jobs: [],
      comps: [],
      scores: [],
    };
  },
 
  created: function() {
    this.getEmployees();
    this.getJobs();
  },
  methods: {
    getScores: function(employee) {
      const compParams = new URLSearchParams();
      let comps = [];
      compParams.append("mode", "getlinks");
      compParams.append("type", "job");
      compParams.append("job_id", employee.job_id);

      axios
        .post(this.url, compParams)
        .then((response) => {
          for (let i = 0; i < response.data.length; i++) {
            this.comps.push(response.data[i]);
            comps.push(response.data[i]);
        
          }
          const scoreParams = new URLSearchParams();
      scoreParams.append("mode", "getlinks");
      scoreParams.append("type", "job");
      scoreParams.append("job_id", employee.job_id);
      scoreParams.append("type", "competency");

      scoreParams.append("employee_id", employee.employee_id);
      axios
        .post(this.url, scoreParams)
        .then((response) => {
          this.scores.length = 0;
          this.scores = [];
          for (let j = 0; j < response.data.length; j++) {
            this.scores.push(response.data[j]);
          }
          if (response.data.length === 0) {
            this.scores.length = 0;
          }
        });

      document.querySelector("#recipe-detail").showModal();
        });
    },
    addJobNameToEmployeeObject: function(employee){
let employee_object = employee;

      let name = "";
      employee_object.name = name;
      const params = new URLSearchParams();
      params.append("mode", "single");
      params.append("type", "job");
      params.append("job_id", employee.job_id);
      axios
        .post(this.url, params)
        .then((response) => {
          for (let j = 0; j < response.data.length; j++) {
            employee_object.name = response.data[j].name;
            name = employee_object.name;
          }
        });
        return employee_object;
    },
    close: function() {
      this.scores.length = 0;
      this.comps.length = 0;
    },
    showForm: function(employee) {

      
      this.currentEmployee = this.addJobNameToEmployeeObject(employee);
      document.querySelector("#form-edit").showModal();
    },
    createEmployee: function(currentEmployee) {
      console.log("Create the recipe...");

      const params = new URLSearchParams();
      params.append("mode", "save");
      params.append("type", "employee");
      params.append("fname", currentEmployee.fname);
      params.append("lname", currentEmployee.lname);
      params.append("email", currentEmployee.email);
      params.append("job_id", currentEmployee.job_id);

      axios
        .post(this.url, params)
        .then((response) => {
         
          this.employees.push(this.addJobNameToEmployeeObject(response.data[0]));
        });
    },
    updateEmployee: function(employee) {
      const params = new URLSearchParams();
      params.append("mode", "save");
      params.append("type", "employee");
      params.append("fname", employee.fname);
      params.append("lname", employee.lname);
      params.append("email", employee.email);
      params.append("employee_id", employee.employee_id);
      console.log(employee.job_id);
      params.append("job_id", employee.job_id);

      axios
        .post(this.url, params)
        .then((response) => {
        let employee_object = this.addJobNameToEmployeeObject(response.data[0]);
          for (let i = 0; i < this.employees.length; i++) {
            if (employee_object.employee_id === this.employees[i].employee_id) {
              this.employees.splice(i, 1, employee_object);
            }
          }  
        });
    },
    deleteEmployee: function(employee) {
      const params = new URLSearchParams();
      params.append("mode", "delete");
      params.append("type", "employee");
      params.append("fname", employee.fname);
      params.append("lname", employee.lname);
      params.append("email", employee.email);
      params.append("job_id", employee.job_id);
      params.append("employee_id", employee.employee_id);
      var x = employee.employee_id;
      axios
        .post(this.url, params)
        .then((response) => {
          console.log("Success deleting recipe", employee);
          console.log(response.data.employee_id);
          for (let i = 0; i < this.employees.length; i++) {
            if (parseInt(x) === this.employees[i].employee_id) {
              this.employees.splice(i, 1);
            }
          }
        });
    },

    getEmployees: function() {
      const params = new URLSearchParams();
      params.append("mode", "all");
      params.append("type", "employee");

      axios
        .post(this.url, params)
        .then((response) => {
          for (let i = 0; i < response.data.length; i++) {
            let employee_object = this.addJobNameToEmployeeObject(response.data[i]);
            this.employees.push(employee_object);
          }
        });
    },
    getJobs: function() {
      const params = new URLSearchParams();
      params.append("mode", "all");
      params.append("type", "job");
      axios
        .post(this.url, params)
        .then((response) => {
          for (let i = 0; i < response.data.length; i++) {
            this.jobs.push(response.data[i]);
            console.log(response.data[i]);
          }
        });
    },
  },
};
</script>
