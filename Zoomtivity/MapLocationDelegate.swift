//
//  MapLocationDelegate.swift
//  Zoomtivity
//
//  Created by Callum Trounce on 17/02/2017.
//  Copyright © 2017 Zoomtivity. All rights reserved.
//

import Foundation
import SKMaps

extension MapViewController : SKPositionerServiceDelegate {
    
    
    
    func positionerService(_ positionerService: SKPositionerService!, updatedCurrentLocation currentLocation: CLLocation!) {
        
        if currentUserLocation == nil {
            var region = SKCoordinateRegion()
            region.center = CLLocationCoordinate2DMake(currentLocation.coordinate.latitude,
                                                       currentLocation.coordinate.longitude);
            region.zoomLevel = 15;
            mapView.visibleRegion = region;
            
            let northEastCoords = mapView.coordinate(for: CGPoint.init(x: mapView.bounds.size.width - 1, y: 1))
            let southWestCoords = mapView.coordinate(for: CGPoint.init(x: 1, y: mapView.bounds.size.height - 1))
            DatabaseManager.sharedDataManager.fetchPoints(type: "food",
                                                          southWestPoint: southWestCoords,
                                                          northEastPoint: northEastCoords,
                                                          completion: { points in
                                                        self.placePointsOnMap(points: points)
            })
            
        }
        
        currentUserLocation = currentLocation
    }
    
    
    func placePointsOnMap(points : [POI]) {
        
        
        
        let pinImageView = UIImageView.init(frame: CGRect.init(x: 0, y: 0, width: 30, height: 30))
        pinImageView.image = UIImage.init(named: "marker-food")
        let annotationView = SKAnnotationView.init(view: pinImageView, reuseIdentifier: "foodPin")
       
        
        for (index, point) in points.enumerated() {
            
            let annotation = SKAnnotation()
            annotation.annotationView = annotationView
            annotation.identifier = Int32(index)
            annotation.location = CLLocationCoordinate2DMake(point.latitude, point.longitude)
            mapView.addAnnotation(annotation, with: SKAnimationSettings.default())
            print(annotation.location)
            
        }
        
    }
    
}
