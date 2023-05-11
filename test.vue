<template>
<!-- 渲染容器 -->


  <div id='app'>
<van-nav-bar title="待呼任务"  class="bluebar" >
  <template #right="click">
<i class="iconfont icon-tongzhi"  style="font-size: 1.2rem;color:white"  ></i>

  </template>

</van-nav-bar>
<van-tag type="primary" id="getuserid"  >{{userid}}</van-tag>
<van-tag type="primary" id="getmissionid"  >{{missionid}}</van-tag>
<van-tag type="primary" id="getdepartmentid"  >{{departmentid}}</van-tag>

<van-dialog v-model="show2" title="申请新任务" show-cancel-button    
  confirmButtonColor="#18b4ed"
  confirmButtonText="申请任务"
  @confirm="yes"
     >

<van-field
  readonly
  clickable
  name="picker"
  :value="value"
  label="请选择任务"
  placeholder="点击选择"
  @click="showPicker = true"

/>
<van-popup v-model="showPicker" position="bottom">
  <van-picker
    show-toolbar
    :columns="columns"
    :key=""
    @confirm="onConfirmType"
    @cancel="showPicker = false"
  :default-index="0"
  >
<template #option="option">
		<div style="display: flex; flex-direction: column; align-items: center;">
{{option.missions_name}}
		</div>
	</template>

 </van-picker>
</van-popup>
</van-dialog>



<van-dialog v-model="show3" title="客户基本信息" show-cancel-button   confirmButtonColor="#0088d7" confirmButtonText="标记为已呼"  @confirm="yes2" >
 <div style="margin-top:2%" >用户号码：<a style="color:#0088d7" >{{tell}}</a></div>
 <div style="margin-top:2%" >套餐金额：<a style="color:#0088d7" >{{price}}</a></div>
 <div style="margin-top:2%" >折扣：<a style="color:#0088d7" >{{discount}}</a>折后价格：<a style="color:#0088d7" >{{after}}</a></div>
 <div style="margin-top:2%">实际套餐费：<a style="color:#0088d7" >{{infact}}</a>月节省费用：<a style="color:#0088d7" >{{surplus}}</a></div>
 <div style="margin-top:2%;width:86%;margin-left:7%">推荐业务：<a style="color:#0088d7" >{{r_business}}</a></div>




<van-button style="margin-top:3%;margin-bottom:3%"onclick="callphone()"  plain hairline round hairline icon="phone-o" type="info">拨 打 电 话</van-button>
</van-dialog>

<van-tag type="primary" id="gettell"  >{{tell}}</van-tag>




  <van-grid :column-num="2"      >

<van-grid-item   v-on:click="upload"  onclick="tonghuajilu()"   > 
<img src=https://zy.huikeyueke.cn/assets/img/button4.png 
 class="iconimg"  >

<span class="van-grid-item__text">上传通话记录</span>
<span class="van-grid-item__text"  style="color:#777" >点击上传</span>
</van-grid-item>


<van-grid-item   v-on:click="request"   > 
<img src=https://zy.huikeyueke.cn/assets/img/cell3.png  class="iconimg"  >

<span class="van-grid-item__text">申请新任务</span>
<span class="van-grid-item__text"  style="color:#777" >点击申请</span>
</van-grid-item>

</van-grid>

 <van-cell title="待呼列表(下拉可刷新)" style="color: #777;"    />

<van-pull-refresh v-model="refreshing" @refresh="onRefresh"  style="margin-top:3%;" >
  <van-list
    v-model="loading"
    :finished="finished"
    finished-text="没有更多了"
    @load="onLoad"
  >



<van-cell v-for="(item,index) in list" :key=index  v-hide="item.tell =='123456'" v-on:click="showtips(item.id,item.tell)"   >

    <van-cell-title  >{{item.tell}}</van-cell-title>
<div class="cell-title" > </div>
      <van-cell-tip class="van-cell--tip"   v-show="item.tell ==searchvalue ||  searchvalue==''"     ><span>点击拨打</span><i class="van-icon van-icon-arrow van-nav-bar__arrow"></i></van-cell-tip>

    <van-tag round class="dark" >{{item.mission_name}}</van-tag>

     </van-cell>
  </van-list>

</van-pull-refresh>





  </div>

</template>
<script>


import axios from 'axios';
import { createApp } from 'vue';
import { Icon } from 'vant';
import { ref } from 'vue';
import { Search } from 'vant';
import { Dialog } from 'vant';
import { Toast } from 'vant';

export default {

  name: 'App',

    data () {
    return {

 show: false,
 show2: false,
 show3: false,
value: '',
tonghuajilu:258,
showPicker: false,
missionid:'',
departmentid:'',
    msg:'',
      list:[],
columns:'',
      loading: false,
      finished: false,
      refreshing: false,
    active:ref(0),
    searchvalue:'',
    baritems:[

    {
    icon:{active: 'iconfont baricon icon-dianhua1',
    inactive:'iconfont baricon icon-dianhua',},
    text:'待呼任务',
    },
    {
    icon:{active: 'iconfont baricon icon-lishixiao1',
    inactive:'iconfont baricon icon-lishixiao',},
    text:'已呼任务',
    },
    {
    icon:{active: 'iconfont baricon icon-my-fill',
    inactive:'iconfont baricon icon-my',},
    text:'个人中心',badge:'3'
    },

    ],

      griditems: [
      {src:'https://zy.huikeyueke.cn/assets/img/button4.png',text:'上传通话记录',color:'#04a800',words:'点击上传',fun:'upload()'},
      {src:'https://zy.huikeyueke.cn/assets/img/cell3.png',text:'申请新任务',words:'点击申请',fun:'request()'},
    
      
      
      ],

    }
  },


setup() {
    const value = ref('');
 
    return {
      value,
    };
  },
  methods: {
 onConfirmType(value,index) {
      this.value = this.columns[index].missions_name;
this.missionid = this.columns[index].id;
      this.showPicker = false;

    },
tonghuajilu(){
},
    onLoad() {






if(this.missionid==''||this.departmentid==''){
 axios.post('https://zy.huikeyueke.cn/api/index/apphomeinfo',
{
userid:localStorage.getItem("userid")}).then((res)=>{
this.missionid=res.data["missionid"];
this.departmentid=res.data["departmentid"];
console.log(res.data);
});
}

if(this.columns==''){
  axios.post('https://zy.huikeyueke.cn/api/index/mymissions',{userid:localStorage.getItem("userid")}).then((res)=>{
this.columns=res.data;
console.log(res.data);

});
}

this.userid=localStorage.getItem("userid");
if(localStorage.getItem("userid")==1||this.userid==0){

this.$router.push({path:'/user'});
}

if(this.list.length==0){
  axios.post('https://zy.huikeyueke.cn/api/index/mymissionlist',{userid:localStorage.getItem("userid")}).then((res)=>{
    console.log(res.data)
    this.list=res.data;
})
}



      setTimeout(() => {
        if (this.refreshing) {
          this.list = [];
          this.refreshing = false;
        }

        //for (let i = 0; i <40; i++) {
         // this.list.push(this.list.length + 1);
        //}
        this.loading = false;

        //if (this.list.length >= 40) {
        //  this.finished = true;
        //}
      }, 500);



    },
       onSearch(val){
console.log(val);
 this.searchvalue = val;

    },
    onCancel(){
console.log(666);
 this.searchvalue = '';
    }, 
  upload(){


  axios.post('https://zy.huikeyueke.cn/api/index/uploadlist',{userid:localStorage.getItem("userid")}).then((res)=>{
   

 this.$toast({
          message:res.data.title,
          position:'center',
           icon: res.data.icon,
        });

})


  },
beforeClose(action, done) {
  if (action === 'confirm') {
    setTimeout(done, 1000);
  } else {
    done();
  }
},

showtips(missionid,mobile){
this.missionid=missionid;
this.mobile=mobile;
axios.post('https://zy.huikeyueke.cn/api/index/tipsinfo',{missionid:missionid}).then((res)=>{
console.log(res.data);
this.show3=true;
  this.tell=res.data["tell"];
this.price=res.data["price"];
this.discount=res.data["discount"];
this.after=res.data["after"];
this.infact=res.data["infact"];
this.r_business=res.data["r_business"];
this.surplus=res.data["surplus"];
Toast(this.tell);
 
})
},
  request(){
this.show2='ture';



  },
yes2(){
axios.post('https://zy.huikeyueke.cn/api/index/settoalready',
{mobile:this.mobile,missionid:this.missionid}).then((res)=>{
Toast(res.data["msg"]);


})
},
yes(){

axios.post('https://zy.huikeyueke.cn/api/index/mymissionlist2',
{userid:localStorage.getItem("userid"),missionid:this.missionid}).then((res)=>{

if(res.data=='error'){
Toast("你还有很多未呼任务！");
}else if(this.list.length>=5){
Toast("你还有很多未呼任务！!");
}
})
},

 
    onRefresh() {
      // 清空列表数据
      this.finished = false;

      // 重新加载数据
      // 将 loading 设置为 true，表示处于加载状态
      this.loading = true;
      this.onLoad();
    },
  },

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
.van-tabbar-item{
   background-color: #e5e5e500;
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
.van-dialog__content {
   height: 175pt;
}
.van-cell--clickable {
position: absolute;
top: 38%;
}
#gettell{
    display: none;
}
</style>


