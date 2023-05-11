<template>
<!-- 渲染容器 -->


  <div id='app'>
<van-nav-bar title="个人中心"  class="bluebar" >
  <template #right="click">


  </template>

</van-nav-bar>


<van-tag type="primary" id="getuserid"  >{{userid}}</van-tag>

<van-dialog v-model="show" title="登录" show-cancel-button    
  confirmButtonColor="#18b4ed"
  confirmButtonText="立即登录"
  @confirm="yes"
     >
 <van-field
    v-model="username"
    name="用户名"
    label="用户名"
    placeholder="用户名"
    :rules="[{ required: true, message: '请填写用户名' }]"
   @input="getusername"
  />

 <van-field
    v-model="password"
    type="password"
    name="密码"
    label="密码"
    placeholder="密码"
    :rules="[{ required: true, message: '请填写密码' }]"
    @input="getpassword"
  />


</van-dialog>


 <van-cell :title="nickname" style="color: #777;"   />

<van-grid :column-num="3" :gutter="10" style="margin-top:3%"  >
  <van-grid-item v-for="(item,index) in list" :key=index :icon="item.icon" :text="item.text" />
</van-grid>

<van-button color="linear-gradient(to right, green, blue)"  block style="margin-top:3%;width:95%;margin-left:2.5%"   v-on:click="login"  v-show="showit==true"  >
  登录
</van-button>

<van-button color="linear-gradient(to right, #ff6034, #ee0a24)"  block style="margin-top:3%;width:95%;margin-left:2.5%"   v-on:click="loginout"   >
  退出登录
</van-button>
  </div>

</template>
<script>

import axios from 'axios';
import { Toast } from 'vant';
export default {

  name: 'App',

    data () {
    return {
 show: false,
username:'',
nickname:'',
password:'',
showit:true,

 list:[

    {
    icon:'chart-trending-o',
    text:'业绩汇总',
    },
     {
    icon:'refund-o',
    text:'收入明细',
    },
  {
    icon:"completed",
    text:'成交任务',
    },

    ],
    }
  },


  methods: {

    onLoad() {

console.log(666);

this.userid=localStorage.getItem("userid");
this.nickname=localStorage.getItem("nickname");

},

getusername(e){
this.tell=e;
},
getpassword(e){
this.password=e;
},
yes(){

axios.post('https://www.huikeyueke.cn/api/index/login',{tell:this.tell,password:this.password}).then((res)=>{

if(res.data["info"]=="success"){
console.log(res.data["nickname"]);
this.userid=res.data["userid"];
this.nickname=res.data["nickname"];
localStorage.setItem("userid", res.data["userid"]);
localStorage.setItem("nickname", res.data["nickname"]);
Toast("登录成功!");
this.show=false;
}else{

Toast("用户名或密码错误!!");
this.show=true; }})   },
login(){
this.show=true;

},
loginout(){
 localStorage.removeItem("userid");
 localStorage.removeItem("nickname");
localStorage.setItem("userid",0);
localStorage.setItem("nickname",0);
Toast("退出登录成功!");
Toast(localStorage.getItem("userid"));

this.onload();
this.$router.push({path:'/'});
},

}
}


</script>

<style>
.van-search__action{
  color:#c5c5c5
}
#app {
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;

}
body{
  background-color:#f3f3f3
}
.bluebar{
  background-color: #18b4ed;
  
}

element.style {
}
.van-cell__value--alone {
 font-size: 15pt;
}
.van-nav-bar__title{
  color:white
}
.dark{
  background: #b6cae0;
}
.van-tabbar{
  background-color: #f3f3f3cf;
  border-top: solid 1px #e7e5e5;
}
.van-cell-tip{
  
}
.van-tabbar-item {
    background-color: #e5e5e585;
}
.appicon{
  font-size: 22pt;
  line-height: 40pt;

}
.baricon{
  font-size: 16pt;


}
.colorred{
color:#ff0063

}
.colorblue1{

  color:#2700ff;
}
.colorblue{
  color:#0082ff;
}
.colororange{
  color:#ff893f;
}
.colorgreen{
  color:#09c145;
}
.iconimg{
  width:3rem;
  height:3rem;
}
.van-grid-item__text{
  font-size:1rem;
}
.van-tabbar-item--active {
      color: #18b4ed;
}
.van-cell--tip{
  position: absolute;
    top: 50%;
    transform: translate(-50%, -50%);
    right: -15%;
    font-size: 14pt;
    color: #8d8d8d;
}
.van-cell--tip span{
      margin-right: 5pt;
}
.van-dialog__message{
  text-align: left;
  line-height: 10pt;
}
.van-dialog__header {
 font-weight:800;
 font-size:17px;
    }

.van-picker-column{
  height: 106pt;
}    
</style>