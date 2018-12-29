--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

--
-- Data for Name: location_table; Type: TABLE DATA; Schema: public; Owner: takoassistant
--

INSERT INTO location_table VALUES (1, 'カブトムシ公園', 'カブトムシ公園,カブトムシ,赤塚山', '豊川市 カブトムシ公園', 34.8465479999999985, 137.359620000000007, 1, 0);
INSERT INTO location_table VALUES (2, '国分尼寺公園天平の里', '三河国分寺跡公園,三河国分寺跡,国分寺跡,国分寺,国分寺跡公園,国分寺公園,天平の里,国分尼寺,三河天平の里', '豊川市 三河国分寺跡公園', 34.8405720000000017, 137.34483800000001, 1, 0);
INSERT INTO location_table VALUES (3, 'チギリ埋立地', '芝生広場,染料埋立地跡芝生広場,埋立地広場,千両広場,千両芝生広場,染料広場,染料埋立地広場,ちぎり埋立地,ちぎりの埋立地,契りの埋立地,契りの埋め立て地', '豊川市 千両埋立地跡芝生広場', 34.8502849999999995, 137.36498499999999, 1, 0);
INSERT INTO location_table VALUES (4, '東三河ふるさと公園', '東三河ふるさと公園,東三河,ふるさと公園,東三河古里公園', '豊川市 東三河ふるさと公園', 34.8405320000000032, 137.313446999999996, 1, 0);
INSERT INTO location_table VALUES (5, '三河臨海埋立公園', '三河臨海緑地公園,三河臨海緑地,臨海緑地公園,臨海緑地,三河臨海公園', '豊川市 三河臨海緑地公園', 34.7954190000000025, 137.315393999999998, 1, 0);
INSERT INTO location_table VALUES (6, '豊川みかみばし', '豊川三上橋,三上橋', '豊川市 豊川三上橋', 34.8199469999999991, 137.431442000000004, 1, 0);
INSERT INTO location_table VALUES (7, '大塚海岸ビーチ', '大塚海岸ビーチ,大塚海岸', '豊川市 大塚海岸ビーチ', 34.8060619999999972, 137.271894000000003, 1, 0);
INSERT INTO location_table VALUES (8, '豊橋運動公園', '総合運動公園サッカー場,総合運動公園,運動公園サッカー場,運動公園,豊橋運動公園,豊橋の運動公園,豊橋のサッカー場', '豊橋市 総合運動公園サッカー場', 34.7539480000000012, 137.325681000000003, 1, 0);
INSERT INTO location_table VALUES (9, '伊古部海岸', '伊古部海岸,伊古部,いこべ海岸', '豊橋市 伊古部海岸', 34.6581249999999983, 137.391068999999987, 1, 0);
INSERT INTO location_table VALUES (10, '赤羽根港', '赤羽根港,赤羽根港ロコステーション,赤羽', '田原市 赤羽根港（ロコステーション）', 34.6063180000000017, 137.190283999999991, 1, 0);
INSERT INTO location_table VALUES (11, '太平洋ロングビーチ', '太平洋ロングビーチ,ロングビーチ,田原のロングビーチ', '田原市 太平洋ロングビーチ', 34.6136729999999986, 137.214881999999989, 1, 0);
INSERT INTO location_table VALUES (12, '伊良湖岬恋路ヶ浜', '伊良湖岬恋路ヶ浜,伊良湖岬,恋路ヶ浜', '田原市 伊良湖岬恋路ヶ浜', 34.5803370000000001, 137.023225999999994, 1, 0);
INSERT INTO location_table VALUES (13, '新居海釣り公園', '新居海釣り公園,海釣り公園,新井公園,浜名湖の新井公園,新居公園,浜名湖の新居公園', '浜名湖 新居海釣り公園', 34.6789229999999975, 137.537636999999989, 1, 0);
INSERT INTO location_table VALUES (14, '浜松ガーデンパーク', 'ガーデンパーク,浜松のガーデンパーク', '浜松市 ガーデンパーク', 34.7138920000000013, 137.596632999999997, 1, 0);
INSERT INTO location_table VALUES (15, '茶臼山山頂', '茶臼山山頂,茶臼山,茶臼山の山頂', '茶臼山山頂', 35.2141349999999989, 137.657486000000006, 1, 0);
INSERT INTO location_table VALUES (16, '茶臼山駐車場', '茶臼山駐車場,茶臼山,茶臼山の駐車場', '茶臼山駐車場', 35.2203500000000034, 137.65474900000001, 1, 0);
INSERT INTO location_table VALUES (17, '鳳来寺山天狗岩', '鳳来寺山天狗岩,鳳来寺山,鳳来寺,天狗岩', '鳳来寺山天狗岩', 34.9794300000000007, 137.593697999999989, 1, 0);
INSERT INTO location_table VALUES (18, '三河湖羽布ダム', '三河湖羽布ダム,三河湖,羽布ダム', '三河湖 羽布ダム', 35.0351950000000016, 137.400047999999998, 1, 0);
INSERT INTO location_table VALUES (19, '海軍工廠平和公園', '海軍工廠公園、海軍工廠平和公園', '豊川市　海軍工廠平和公園', 34.8376220000000032, 137.370077000000009, 1, 0);
INSERT INTO location_table VALUES (20, '中田島砂丘', '中田島砂丘、中田島', '浜松市　中田島砂丘', 34.6660380000000004, 137.743284999999986, 1, 0);
INSERT INTO location_table VALUES (21, '宇連ダム', '宇連ダム', '宇連ダム', 35.0090270000000032, 137.648583000000002, 1, 0);


--
-- Name: location_table_pkey_seq; Type: SEQUENCE SET; Schema: public; Owner: takoassistant
--

SELECT pg_catalog.setval('location_table_pkey_seq', 1, false);


--
-- PostgreSQL database dump complete
--

