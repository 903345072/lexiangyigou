var app = getApp();
Component({
  properties: {
    parameter:{
      type: Object,
      value:{
        class:'0'
      },
    },
    logoUrl:{
      type:String,
      value:'',
    }
  },
  data: {
    navH: ""
  },
  ready: function(){
    this.setClass();
    var pages = getCurrentPages();
    if (pages.length <= 1) this.setData({'parameter.return':0});
  },
  attached: function () {
    this.setData({
      navH: app.globalData.navHeight
    });
  },
  methods: {
    return: function () {
      wx.navigateBack({
        delta: 1
      });
    },
    setGoodsSearch:function(){
       wx.navigateTo({
         url: '/pages/goods_search/index',
       })
    },
    setClass:function(){
      var color = '';
      switch (this.data.parameter.class) {
        case "0": case 'pink':
          color = 'pink'
          break;
        case '1': case 'black':
          color = 'black'
          break;
        case '2': case 'gray':
          color = 'gray'
          break;
        case '3': case "red":
          color = 'red'
          break;
        case '4': case "gradual01":
          color = 'gradual01'
          break;
        case '5': case "gradual02":
          color = 'gradual02'
          break;
        case '6': case "pink":
          color = 'pink'
          break;
        case '7': case "on":
          color = 'on'
          break;
        default:
          break;
      }
      this.setData({
        'parameter.class': color
      })
    }
  }
})