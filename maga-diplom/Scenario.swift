//
//  Scenario.swift
//  maga-diplom
//
//  Created by Dmytro Ostapchenko on 04.12.2024.
//

import Foundation
import CoreLocation
import UIKit.UIColor

struct MovingPart {
    let movingCoordinatesDropAmount: Int
    let movingTime: Int
}

enum ScenarioAction {
    struct ShowRouteInput {
        let directionCoordinates: [CLLocationCoordinate2D]
    }
    
    case showRoute(ShowRouteInput)
    case waitFor(Int)
    
    struct ShowPointInput {
        let color: UIColor
        let coordinate: CLLocationCoordinate2D
    }
    
    case showPoint(ShowPointInput)
    
    struct ShowSubRouteInput {
        let directionCoordinates: [CLLocationCoordinate2D]
        let movingPart: MovingPart?
    }
    
    case showSubRoute(ShowSubRouteInput)
    case showYourRouteAreDeletedAlert
    case showDeleteRouteAlert
}


class ScenarioDataProvider {
    static var abPoints1: [CLLocationCoordinate2D] {
        return [
            CLLocationCoordinate2D(latitude: 50.450019956399785, longitude: 30.444089211523536),
            CLLocationCoordinate2D(latitude: 50.450201420372004, longitude: 30.444164313375946),
            CLLocationCoordinate2D(latitude: 50.45044692934428, longitude: 30.444255508482453),
            CLLocationCoordinate2D(latitude: 50.45068368418138, longitude: 30.44434670358896),
            CLLocationCoordinate2D(latitude: 50.45107072988934, longitude: 30.44448684900999),
            CLLocationCoordinate2D(latitude: 50.45149342373593, longitude: 30.44466219842434),
            CLLocationCoordinate2D(latitude: 50.45177756581029, longitude: 30.444804690778255),
            CLLocationCoordinate2D(latitude: 50.45201431398901, longitude: 30.445082969963554),
            CLLocationCoordinate2D(latitude: 50.45248759331604, longitude: 30.445290841162205),
            CLLocationCoordinate2D(latitude: 50.453023415797844, longitude: 30.445473231375217),
            CLLocationCoordinate2D(latitude: 50.45322279998485, longitude: 30.445744469761852),
            CLLocationCoordinate2D(latitude: 50.453265921507914, longitude: 30.446153506636616),
            CLLocationCoordinate2D(latitude: 50.45341321750317, longitude: 30.446541421115395),
            CLLocationCoordinate2D(latitude: 50.453576950339425, longitude: 30.44670503586531),
            CLLocationCoordinate2D(latitude: 50.453837384713204, longitude: 30.44677309691906),
            CLLocationCoordinate2D(latitude: 50.4540487197497, longitude: 30.44687367975712),
            CLLocationCoordinate2D(latitude: 50.454233370296656, longitude: 30.447287410497665),
            CLLocationCoordinate2D(latitude: 50.45389715633442, longitude: 30.449358075857166),
            CLLocationCoordinate2D(latitude: 50.453277449037145, longitude: 30.45304510742426)
        ]
    }
    
    static var abPoints2: [CLLocationCoordinate2D] {
        return [
            CLLocationCoordinate2D(latitude: 50.450022091274086, longitude: 30.44410027563572),
            CLLocationCoordinate2D(latitude: 50.45038309713404, longitude: 30.444278307259083),
            CLLocationCoordinate2D(latitude: 50.451494704620636, longitude: 30.444688014686104),
            CLLocationCoordinate2D(latitude: 50.45133587465275, longitude: 30.44553995132446),
            CLLocationCoordinate2D(latitude: 50.451180246384745, longitude: 30.446434132754803),
            CLLocationCoordinate2D(latitude: 50.450960572690825, longitude: 30.447754450142384),
            CLLocationCoordinate2D(latitude: 50.45071826864813, longitude: 30.44929537922144),
            CLLocationCoordinate2D(latitude: 50.450484929749614, longitude: 30.45086547732353),
            CLLocationCoordinate2D(latitude: 50.45028233173055, longitude: 30.452064760029316),
            CLLocationCoordinate2D(latitude: 50.45013545296143, longitude: 30.453050471842293),
            CLLocationCoordinate2D(latitude: 50.450905493995364, longitude: 30.453042425215244),
            CLLocationCoordinate2D(latitude: 50.45154337821374, longitude: 30.452996492385868),
            CLLocationCoordinate2D(latitude: 50.45214987260214, longitude: 30.45297771692276),
            CLLocationCoordinate2D(latitude: 50.45289426392203, longitude: 30.45301459729672),
            CLLocationCoordinate2D(latitude: 50.45329025739964, longitude: 30.453051142394543)
        ]
    }
    
    static var abPoints3: [CLLocationCoordinate2D] {
        return [
            CLLocationCoordinate2D(latitude: 50.45001760803794, longitude: 30.444073453545574),
            CLLocationCoordinate2D(latitude: 50.45043710900984, longitude: 30.44421795755625),
            CLLocationCoordinate2D(latitude: 50.45094093223311, longitude: 30.444420464336872),
            CLLocationCoordinate2D(latitude: 50.45151242352217, longitude: 30.444659851491455),
            CLLocationCoordinate2D(latitude: 50.451346335244985, longitude: 30.445453114807602),
            CLLocationCoordinate2D(latitude: 50.451194976654286, longitude: 30.446374788880345),
            CLLocationCoordinate2D(latitude: 50.45109592084055, longitude: 30.44706042855978),
            CLLocationCoordinate2D(latitude: 50.450957583926034, longitude: 30.44784497469664),
            CLLocationCoordinate2D(latitude: 50.45080921286601, longitude: 30.44877301901579),
            CLLocationCoordinate2D(latitude: 50.450716987742425, longitude: 30.449384227395058),
            CLLocationCoordinate2D(latitude: 50.45059273972223, longitude: 30.450091660022732),
            CLLocationCoordinate2D(latitude: 50.45007973284397, longitude: 30.449901223182675),
            CLLocationCoordinate2D(latitude: 50.44961539597995, longitude: 30.449710115790364),
            CLLocationCoordinate2D(latitude: 50.44906864682388, longitude: 30.44946201145649),
            CLLocationCoordinate2D(latitude: 50.44893670875212, longitude: 30.450451746582985),
            CLLocationCoordinate2D(latitude: 50.448819714814405, longitude: 30.451136045157913),
            CLLocationCoordinate2D(latitude: 50.448719586561346, longitude: 30.451787151396275),
            CLLocationCoordinate2D(latitude: 50.448587861010175, longitude: 30.452581755816933),
            CLLocationCoordinate2D(latitude: 50.44852445328024, longitude: 30.45311216264963),
            CLLocationCoordinate2D(latitude: 50.44933145441098, longitude: 30.45306321233511),
            CLLocationCoordinate2D(latitude: 50.45002657450982, longitude: 30.453036054968837),
            CLLocationCoordinate2D(latitude: 50.450656998618776, longitude: 30.453058183193207),
            CLLocationCoordinate2D(latitude: 50.451240021363056, longitude: 30.4530256614089),
            CLLocationCoordinate2D(latitude: 50.451787172387064, longitude: 30.452993810176853),
            CLLocationCoordinate2D(latitude: 50.4522427353649, longitude: 30.45298509299755),
            CLLocationCoordinate2D(latitude: 50.45278667266873, longitude: 30.45302130281925),
            CLLocationCoordinate2D(latitude: 50.45326122510634, longitude: 30.45302465558052)
        ]
    }
    
    static var aPoint: CLLocationCoordinate2D {
        return .init(latitude: 50.44841386316409, longitude: 30.44349879026413)
    }
    
    static var bPoint: CLLocationCoordinate2D {
        return .init(latitude: 50.45104980858065, longitude: 30.464396215975285)
    }
    
    static var specialPoint1: CLLocationCoordinate2D {
        return .init(latitude: 50.455180520211540, longitude: 30.441340282559395) // 50.45518052021154, 30.441340282559395
    }
    
    static var specialPoint2: CLLocationCoordinate2D {
        return .init(latitude: 50.45401371077859, longitude: 30.448431707918644) //50.45401371077859, longitude: 30.448431707918644)
    }
    
    static var specialPoint3: CLLocationCoordinate2D {
        return .init(latitude: 50.4505634922886, longitude: 30.450318306684494) //  50.4505634922886, 30.450318306684494
    }
    
    static var movingDirection: [CLLocationCoordinate2D] {
        return [
            CLLocationCoordinate2D(latitude: 50.45001056295169, longitude: 30.44411066919565),
            CLLocationCoordinate2D(latitude: 50.4501873302532, longitude: 30.4441824182868),
            CLLocationCoordinate2D(latitude: 50.45035811928918, longitude: 30.44425416737795),
            CLLocationCoordinate2D(latitude: 50.45054086287499, longitude: 30.44432122260332),
            CLLocationCoordinate2D(latitude: 50.45071165063486, longitude: 30.44440235942602),
            CLLocationCoordinate2D(latitude: 50.45090634792913, longitude: 30.44446505606175),
            CLLocationCoordinate2D(latitude: 50.45110040397453, longitude: 30.444538816809654),
            CLLocationCoordinate2D(latitude: 50.451282504248134, longitude: 30.444631353020668),
            CLLocationCoordinate2D(latitude: 50.45148018792528, longitude: 30.444731265306473)
        ]
    }
    
    static func clientScenarioActions() -> [ScenarioAction] {
        return [
            .showRoute(.init(directionCoordinates: ScenarioDataProvider.abPoints1)),
            .showSubRoute(
                .init(
                    directionCoordinates: ScenarioDataProvider.abPoints1,
                    movingPart: .init(
                        movingCoordinatesDropAmount: 5,
                        movingTime: 180
                    )
                )
            ),
            .waitFor(17),
            .showPoint(.init(color: .systemRed, coordinate: ScenarioDataProvider.specialPoint1)),
            .waitFor(17),
            .showPoint(.init(color: .systemRed, coordinate: ScenarioDataProvider.specialPoint2)),
            .waitFor(1),
            .showRoute(.init(directionCoordinates: ScenarioDataProvider.abPoints2)),
            .showSubRoute(
                .init(
                    directionCoordinates: ScenarioDataProvider.abPoints2,
                    movingPart: nil
                )
            ),
            .waitFor(17),
            .showPoint(.init(color: .systemRed, coordinate: ScenarioDataProvider.specialPoint3)),
            .waitFor(1),
            .showRoute(.init(directionCoordinates: ScenarioDataProvider.abPoints3)),
            .showSubRoute(
                .init(
                    directionCoordinates: ScenarioDataProvider.abPoints3,
                    movingPart: nil
                )
            ),
            .waitFor(18),
            .showYourRouteAreDeletedAlert
        ]
    }
    
    static func adminScenarioActions() -> [ScenarioAction] {
        return [
            .waitFor(1),
            .showRoute(.init(directionCoordinates: ScenarioDataProvider.abPoints1)),
            .showSubRoute(
                .init(
                    directionCoordinates: ScenarioDataProvider.abPoints1,
                    movingPart: .init(
                        movingCoordinatesDropAmount: 5,
                        movingTime: 180
                    )
                )
            ),
            .waitFor(17),
            .showPoint(.init(color: .systemRed, coordinate: ScenarioDataProvider.specialPoint1)),
            .waitFor(17),
            .showPoint(.init(color: .systemRed, coordinate: ScenarioDataProvider.specialPoint2)),
            .waitFor(1),
            .showRoute(.init(directionCoordinates: ScenarioDataProvider.abPoints2)),
            .showSubRoute(
                .init(
                    directionCoordinates: ScenarioDataProvider.abPoints2,
                    movingPart: nil
                )
            ),
            .waitFor(17),
            .showPoint(.init(color: .systemRed, coordinate: ScenarioDataProvider.specialPoint3)),
            .waitFor(1),
            .showRoute(.init(directionCoordinates: ScenarioDataProvider.abPoints3)),
            .showSubRoute(
                .init(
                    directionCoordinates: ScenarioDataProvider.abPoints3,
                    movingPart: nil
                )
            ),
            .waitFor(12),
            .showDeleteRouteAlert
        ]
    }
}
