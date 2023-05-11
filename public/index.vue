<template>
<!-- 渲染容器 -->


  <div id='app1'>


<van-dialog v-model="show" title="更新用户信息" show-cancel-button    
  confirmButtonColor="#18b4ed"
  confirmButtonText="确认更新"
  @confirm="yes"
     >

 <van-cell title="营销情况(滑动选择)" style="color: #18b4ed;margin-top:6%"    />
<van-picker
  title="标题"
  :columns="columns"
  @cancel="onCancel"
  @change="onChange"
  :default-index="4"

/>
<van-field
  style="margin-top: -33%;z-index: 99999;"
  autosize
  label="备注"
  type="textarea"
  maxlength="52"
  placeholder="请输入备注"
   @input="getfieldvalue"

/>
</van-dialog>

<van-nav-bar title="已呼任务"  class="bluebar" >
  <template #right="click">


  </template>

</van-nav-bar>

<form action="/">
  <van-search
  show-action
    v-model="value"
    placeholder="请输入号码搜索"
    @search="onSearch"
    @cancel="onCancel"

  />
</form>


<van-pull-refresh v-model="refreshing" @refresh="onRefresh"  style="margin-top:3%;" >
  <van-list
    v-model="loading"
    :finished="finished"
    finished-text="没有更多了"
    @load="onLoad"
  >
    <van-cell v-for="(item,index) in list" :key=index  v-show="item.tell ==searchvalue ||  searchvalue==''" v-on:click="showtips(item.id,item.tell)" >
    <van-cell-title >{{item.tell}}</van-cell-title>
      <van-cell-tip class="van-cell--tip"   v-show="item.tell ==searchvalue ||  searchvalue==''"  ><span>更新信息</span><i class="van-icon van-icon-arrow van-nav-bar__arrow"></i></van-cell-tip>
    <br />


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

  name: 'App1',

    data () {
    return {
     columns: [],
 show: false,
	userid:0,
   value1: 0,
    msg:'',
      list: [],
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
      {src:'https://www.huikeyueke.cn/assets/img/button4.png',text:'上传通话记录',color:'#04a800',words:'点击上传',fun:'upload()'},
      {src:'https://www.huikeyueke.cn/assets/img/cell3.png',text:'申请新任务',words:'点击申请',fun:'request()'},
    
      
      
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
 onChange(picker, value, index) {
      Toast(`当前值：${value}, 当前索引：${index}`);
this.mktsit=index;
    },
getfieldvalue(e){
this.remarks=e;
},
showtips(missionid,tell){
this.show=true;
this.tell=tell;
this.missionid=missionid;
},
yes(){
if(this.mktsit==undefined){
Toast('请选择营销情况！');
}else{

axios.post('https://www.huikeyueke.cn/api/index/remarks',{userid:localStorage.getItem("userid"),nickname:localStorage.getItem("nickname"),tell:this.tell,missionid:this.missionid,remarks:this.remarks,MKT_SIT:this.mktsit}).then((res)=>{


Toast(res.data["info"]);
})

Toast('tell=='+this.tell+'missionid==='+this.missionid+'mktsit==='+this.mktsit+'remarks==='+this.remarks);
}
 
},

    onLoad() {
this.userid=localStorage.getItem("userid");
if(localStorage.getItem("userid")==1||this.userid==0){
Toast('请先登录 ！');
this.$router.push({path:'/user'});
}



  axios.post('https://www.huikeyueke.cn/api/index/alreadymissionlist',{userid:localStorage.getItem("userid")}).then((res)=>{
    console.log(res.data)
    this.list=res.data;
})

if(this.columns.length==0){
  axios.post('https://www.huikeyueke.cn/api/index/skilist',{userid:localStorage.getItem("userid")}).then((res)=>{
    console.log("columns:"+res.data)
    this.columns=res.data;
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
      }, 1000);



    },
       onSearch(val){
console.log(val);
 this.searchvalue = val;

    },
    onCancel(){
console.log(666);
 this.searchvalue = '';
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
.van-dialog__content {
    height: 226pt;
}
.van-picker-column{
  height: 106pt;
}    
</style>