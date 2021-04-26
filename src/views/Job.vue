<template>
  <div class="job">
    <form action="#">
      <p>Job name: <input type="text" v-model="newName" required /></p>
      <br />
      <button v-on:click="createJob(currentJob)">Create</button>
    </form>
    <br />
    <table>
      <thead>
        <th>name</th>
        <th></th>
        <th>competencies</th>
      </thead>
      <tbody>
        <tr v-for="job in jobs">
          <td>{{ job.name }}</td>
          <td>
            <button v-on:click="showForm(job)">Edit</button>
          </td>
          <td><button v-on:click="showJob(job)">competencies</button></td>

          <dialog id="form-edit">
            <form method="dialog">
              <p>
                Job name:
                <input type="text" v-model="currentJob.name" required />
              </p>

              <button class="btn" v-on:click="updateJob(currentJob)">
                Update
              </button>
              <button class="btn" v-on:click="destroyJob(currentJob)">
                Destroy
              </button>

              <button class="btn">Close</button>
            </form>
          </dialog>
        </tr>
      </tbody>
    </table>

    <dialog id="recipe-details">
      <form method="dialog">
        <button class="btn" v-on:click="close()">Close</button>
        <button class="btn" v-on:click="linkOrUnlinkComp(comps, currentJob)">
          Apply
        </button>

        <div v-for="comp in comps">
          <div class="checkbox-container">
            <input
              type="checkbox"
              v-model="comp.toggle"
              true-value="yes"
              false-value="no"
            />
            <span> {{ comp.name }}</span>
          </div>
        </div>
      </form>
    </dialog>
  </div>
</template>

<style></style>

<script>
var axios = require("axios");

export default {
  data: function() {
    return {
      url: "http://localhost/project2/data_p2.php",
      jobs: [],
      comps: [],
      currentComp: {},
      currentJob: {},
      toggle: "no",
      checkedComps: [],
      newName: "",
    };
  },
  created: function() {
    this.getRecipe();
  },
  methods: {
    getRecipe: function() {
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
    showForm: function(job) {
      console.log(job);
      this.currentJob = job;
      document.querySelector("#form-edit").showModal();
    },
    getCompLink: function(job) {
      let checkedComps = [];
      const params = new URLSearchParams();
      params.append("mode", "getlinks");
      params.append("type", "job");
      params.append("job_id", job.job_id);
      axios
        .post(this.url, params)
        .then((response) => {
          for (let i = 0; i < response.data.length; i++) {
            checkedComps.push(response.data[i]);
          }
          this.getComp(checkedComps);
        });
    },
    getComp: function(checkedComps) {
      this.comps.length = 0;
      const param = new URLSearchParams();
      param.append("mode", "all");
      param.append("type", "competency");
      axios
        .post(this.url, param)
        .then((response) => {
          for (let i = 0; i < response.data.length; i++) {
            this.comps.push(response.data[i]);
            for (let j = 0; j < checkedComps.length; j++) {
              if (
                response.data[i].competency_id === checkedComps[j].competency_id
              ) {
                this.comps[i].toggle = "yes";
              }
            }
          }
        });
    },
    createJob: function(job) {
      console.log("Create the recipe...");
      const params = new URLSearchParams();
      params.append("mode", "save");
      params.append("type", "job");
      params.append("job", this.newName);

      axios
        .post(this.url, params)
        .then((response) => {
          this.jobs.push(response.data[0]);
        });
    },
    updateJob: function(job) {
      const params = new URLSearchParams();
      params.append("mode", "save");
      params.append("type", "job");
      params.append("job_id", job.job_id);
      params.append("job", job.name);

      axios
        .post(this.url, params)
        .then((response) => {
          for (let i = 0; i < this.jobs.length; i++) {
            if (response.data[0].job_id === this.jobs[i].job_id) {
              this.jobs.splice(i, 1, response.data[0]);
            }
          }
        });
    },
    destroyJob: function(job) {
      const params = new URLSearchParams();
      params.append("mode", "delete");
      params.append("type", "job");
      params.append("job_id", job.job_id);
      axios
        .post(this.url, params)
        .then((response) => {
          for (let i = 0; i < this.jobs.length; i++) {
            if (parseInt(job.job_id) === this.jobs[i].job_id) {
              this.jobs.splice(i, 1);
            }
          }
        });
    },
    showJob: function(job) {
      this.currentJob = job;
      this.getCompLink(this.currentJob);
      document.querySelector("#recipe-details").showModal();
    },
    close: function() {
      this.comps.length = 0;
    },
    linkOrUnlinkComp: function(comps, job) {
      console.log(job);
      for (let i = 0; i < comps.length; i++) {
        if (comps[i].toggle === "yes") {
          const params = new URLSearchParams();
          params.append("mode", "link");
          params.append("type", "job");
          params.append("job_id", job.job_id);
          params.append("type", "competency");
          params.append("competency_id", comps[i].competency_id);
          axios
            .post(this.url, params)
            .then((response) => {
            });
        } else {
          const params = new URLSearchParams();
          params.append("mode", "unlink");
          params.append("type", "job");
          params.append("job_id", job.job_id);
          params.append("type", "competency");
          params.append("competency_id", comps[i].competency_id);
          axios
            .post(this.url, params)
            .then((response) => {
            });
        }
      }
    },
  },
};
</script>
