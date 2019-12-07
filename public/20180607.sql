CREATE TABLE `tck_theme` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `period` int(10) NOT NULL DEFAULT 0 COMMENT '主题期号',
  `title` varchar(64) NOT NULL DEFAULT '' COMMENT '主题标题',
  ` ` text(5000) NOT NULL DEFAULT '' COMMENT '主题内容',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '童创课课程视频-主题表'

CREATE TABLE `tck_course_video_class` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '类型名称',
  `pid` int(5) NULL COMMENT '父类ID',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1,主类;2,副类',
  `display` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1,显示;0,禁用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '童创课课程视频-视频类型表'
insert into `tck_course_video_class` (`name`, `pid`, `type`, `display`) value('主题引导视频',0,1,1);
insert into `tck_course_video_class` (`name`, `pid`, `type`, `display`) value('课程视频',0,1,1);
insert into `tck_course_video_class` (`name`, `pid`, `type`, `display`) value('专家视频',0,1,1);
insert into `tck_course_video_class` (`name`, `pid`, `type`, `display`) value('点评视频',0,1,1);
insert into `tck_course_video_class` (`name`, `pid`, `type`, `display`) value('3-5岁',2,2,1);
insert into `tck_course_video_class` (`name`, `pid`, `type`, `display`) value('4-6岁',2,2,1);
insert into `tck_course_video_class` (`name`, `pid`, `type`, `display`) value('5-7岁',2,2,1);

CREATE TABLE `tck_course_video` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `theme_id` int(11) NOT NULL DEFAULT 0 COMMENT '主题ID',
  `video_class_id` tinyint(2) NOT NULL DEFAULT 0 COMMENT '视频类型',
  `cover_img` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图片',
  `video_name` varchar(125) NOT NULL DEFAULT '' COMMENT '视频名称',
  `video_introduction` varchar(500) NOT NULL DEFAULT '' COMMENT '视频简介',
  `video_duration` varchar(15) NOT NULL DEFAULT '' COMMENT '视频时长(分:秒)',
  `video_url` varchar(255) NOT NULL DEFAULT '' COMMENT '视频链接',
  `lecturer` varchar(50) NOT NULL DEFAULT '' COMMENT '主讲人',
  `play_num` int(10) NOT NULL DEFAULT 0 COMMENT '播放次数',
  `age_part` varchar(50) NOT NULL DEFAULT '' COMMENT '年龄段',
  `detail` text(5000) NOT NULL DEFAULT '' COMMENT '详情',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态;1,上线;0,下线',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '童创课课程视频-视频表'

CREATE TABLE `tck_baby_works` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `baby_id` int(11) NOT NULL DEFAULT 0 COMMENT '宝宝姓名',
  `cycle_id` int(11) NOT NULL DEFAULT 0 COMMENT '期号id',
  `works_score` int(3) NOT NULL DEFAULT 0 COMMENT '作品得分(0-100)',
  `teacher_id` int(11) NOT NULL DEFAULT 0 COMMENT '创客老师',
  `score_show` tinyint(1) NOT NULL DEFAULT 0 COMMENT '得分是否可看;1,显示;0,不显示',
  `submit_time` datetime DEFAULT NULL COMMENT '作品提交时间',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '童创课宝宝作品表'
alter table `tck_baby_works` add column `user_id` int(11) default 0 comment '用户id' after `id`;
alter table `tck_baby_works` add column `creative_explanation` varchar(300) default '' comment '创意说明' after `teacher_id`;
alter table `pic` modify column `pic_type` int(10) DEFAULT NULL COMMENT '1.会员页轮播图 2.礼包页轮播图 3-商品轮播图 10-宝宝作品图';

CREATE TABLE `tck_works_review` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `score_group` tinyint(1) NOT NULL DEFAULT 0 COMMENT '分数段;1,40-60;2,61-70;3,71-80;4,81-90;5,91-100',
  `review` varchar(500) NOT NULL DEFAULT '' COMMENT '评语',
  `usage_count` int(10) NOT NULL DEFAULT 0 COMMENT '使用次数',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态;1,可用;0,禁用',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '童创课评语表'

CREATE TABLE `tck_baby_review` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `works_id` int(11) NOT NULL DEFAULT 0 COMMENT '作品id',
  `review_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '评语类型;1,自动评语;2,人工评语',
  `artificial_review` varchar(500) NOT NULL DEFAULT '' COMMENT '人工评语',
  `automatic_review` int(11) NOT NULL DEFAULT 0 COMMENT '自动评语id',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '童创课宝宝作品评语表'

CREATE TABLE `tck_contest_cycle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `period` int(10) NOT NULL DEFAULT 0 COMMENT '赛期期号',
  `title` varchar(64) NOT NULL DEFAULT '' COMMENT '赛期标题',
  `introduction` varchar(300) NOT NULL DEFAULT '' COMMENT '赛期简介',
  `detail` text(5000) NULL COMMENT '赛期详情',
  `content_type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '上传内容;1,图片;2,视频;3,两者都有',
  `content_num` tinyint(3) NOT NULL DEFAULT 0 COMMENT '上传内容最大数量',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态;0,未开始;1,进行中;2,已结束',
  `start_time` datetime DEFAULT NULL COMMENT '开始时间',
  `end_time` datetime DEFAULT NULL COMMENT '截止时间',
  `result_time` datetime DEFAULT NULL COMMENT '出结果时间',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '童创课赛期表'